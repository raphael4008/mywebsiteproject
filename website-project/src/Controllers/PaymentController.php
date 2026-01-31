<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Config\Config;
use App\Models\Reservation;
use App\Models\Payment as PaymentModel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PaymentController extends BaseController {
    private $httpClient;
    private $mpesaBaseUrl;

    public function __construct() {
        $this->httpClient = new Client([
            'timeout' => 10.0,
        ]);
        $this->mpesaBaseUrl = Config::get('MPESA_ENV') === 'live'
            ? Config::get('MPESA_LIVE_URL')
            : Config::get('MPESA_SANDBOX_URL');
    }

    public function processMpesaPayment($data) {
        $listingId = $data['listing_id'] ?? null;
        $userId = $data['user_id'] ?? null;
        $phone = $data['phone'] ?? null;

        if (!$listingId || !$userId || !$phone) {
            return $this->jsonErrorResponse('Listing ID, User ID, and Phone Number are required.', 400);
        }

        $reservationId = Reservation::create([
            'listing_id' => $listingId,
            'user_id' => $userId,
            'status' => 'PENDING'
        ]);

        $listing = \App\Models\Listing::find($listingId);
        $amountToPay = (int) ($listing['rent_amount'] ?? 1); // Use 1 for sandbox if rent is 0

        $paymentId = PaymentModel::create([
            'reservation_id' => $reservationId,
            'amount' => $amountToPay,
            'payment_method' => 'mpesa',
            'status' => 'pending'
        ]);

        $consumerKey = Config::get('MPESA_CONSUMER_KEY');
        $consumerSecret = Config::get('MPESA_CONSUMER_SECRET');
        $shortCode = Config::get('MPESA_SHORTCODE');
        $passkey = Config::get('MPESA_PASSKEY');
        $callbackUrl = Config::get('MPESA_CALLBACK_URL') . '?payment_id=' . $paymentId;

        $accessToken = $this->getMpesaAccessToken($consumerKey, $consumerSecret);
        if (!$accessToken) {
            return $this->jsonErrorResponse('Failed to get M-Pesa access token.', 500);
        }

        $response = $this->initiateStkPush($accessToken, $shortCode, $passkey, $phone, $amountToPay, $callbackUrl);

        if (isset($response->ResponseCode) && $response->ResponseCode == '0') {
            PaymentModel::update($paymentId, ['transaction_id' => $response->CheckoutRequestID]);
            $this->jsonResponse(['success' => true, 'mpesaResponse' => $response]);
        } else {
            PaymentModel::update($paymentId, ['status' => 'failed']);
            Reservation::update($reservationId, ['status' => 'FAILED']);
            $this->jsonErrorResponse('M-Pesa STK push failed', 500, ['mpesaResponse' => $response]);
        }
    }

    private function getMpesaAccessToken($consumerKey, $consumerSecret) {
        $url = $this->mpesaBaseUrl . '/oauth/v1/generate?grant_type=client_credentials';
        
        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($consumerKey . ':' . $consumerSecret)
                ]
            ]);
            $result = json_decode($response->getBody()->getContents());
            return $result->access_token ?? null;
        } catch (RequestException $e) {
            error_log("Guzzle error getting M-Pesa token: " . $e->getMessage());
            return null;
        }
    }

    private function initiateStkPush($accessToken, $shortCode, $passkey, $phone, $amount, $callbackUrl) {
        $url = $this->mpesaBaseUrl . '/mpesa/stkpush/v1/processrequest';
        $timestamp = date('YmdHis');
        $password = base64_encode($shortCode . $passkey . $timestamp);

        $payload = [
            'BusinessShortCode' => $shortCode, 'Password' => $password, 'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline', 'Amount' => $amount, 'PartyA' => $phone,
            'PartyB' => $shortCode, 'PhoneNumber' => $phone, 'CallBackURL' => $callbackUrl,
            'AccountReference' => 'HouseHunting', 'TransactionDesc' => 'Payment for house reservation'
        ];

        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $accessToken],
                'json' => $payload
            ]);
            return json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            error_log("Guzzle error initiating STK push: " . $e->getMessage());
            return null;
        }
    }

    public function handleMpesaCallback($data = null) {
        $logPath = __DIR__ . '/../../../storage/logs/mpesa_callbacks.log';
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) { mkdir($logDir, 0755, true); }

        try {
            $raw = file_get_contents('php://input');
            if (empty($raw)) {
                http_response_code(400);
                echo json_encode(['error' => 'Empty payload']);
                return;
            }
            file_put_contents($logPath, date('c') . ' ' . $raw . PHP_EOL, FILE_APPEND | LOCK_EX);
            
            $payload = json_decode($raw, true);
            if (!is_array($payload) || !isset($payload['Body']['stkCallback'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON or missing stkCallback']);
                return;
            }

            $callback = $payload['Body']['stkCallback'];
            $checkoutRequestID = $callback['CheckoutRequestID'] ?? null;
            $resultCode = $callback['ResultCode'] ?? null;

            if (!$checkoutRequestID) return; // Nothing to process

            $payments = PaymentModel::where(['transaction_id' => $checkoutRequestID]);
            if (empty($payments)) {
                error_log("No payment found for CheckoutRequestID: $checkoutRequestID");
                return;
            }

            $payment = $payments[0];
            $paymentId = $payment['id'];
            $reservationId = $payment['reservation_id'];

            if ($resultCode === 0 || $resultCode === '0') {
                $metadata = $callback['CallbackMetadata']['Item'] ?? [];
                $updateData = ['status' => 'completed'];
                foreach ($metadata as $item) {
                    if ($item['Name'] === 'MpesaReceiptNumber') $updateData['receipt_number'] = $item['Value'];
                    if ($item['Name'] === 'Amount') $updateData['amount_paid'] = $item['Value'];
                    if ($item['Name'] === 'PhoneNumber') $updateData['phone'] = $item['Value'];
                }
                PaymentModel::update($paymentId, $updateData);
                if ($reservationId) Reservation::update($reservationId, ['status' => 'CONFIRMED']);
            } else {
                PaymentModel::update($paymentId, ['status' => 'failed']);
                if ($reservationId) Reservation::update($reservationId, ['status' => 'FAILED']);
            }
            $this->jsonResponse(['ResultCode' => 0, 'ResultDesc' => 'Received']);
        } catch (\Throwable $t) {
            error_log('Unhandled exception in handleMpesaCallback: ' . $t->getMessage());
            $this->jsonErrorResponse('Internal server error', 500);
        }
    }

    public function queryMpesaTransactionStatus($data) {
        $paymentId = $data['payment_id'] ?? null;
        if (!$paymentId) return $this->jsonErrorResponse('Payment ID is required.', 400);
        
        $payment = PaymentModel::find($paymentId);
        if (!$payment) return $this->jsonErrorResponse('Payment not found.', 404);
        
        $checkoutRequestID = $payment['transaction_id'] ?? null;
        if (!$checkoutRequestID) return $this->jsonErrorResponse('Transaction not initiated.', 422);

        $accessToken = $this->getMpesaAccessToken(Config::get('MPESA_CONSUMER_KEY'), Config::get('MPESA_CONSUMER_SECRET'));
        if (!$accessToken) return $this->jsonErrorResponse('Failed to get M-Pesa access token.', 500);

        $response = $this->_queryStkStatus($accessToken, $checkoutRequestID);
        if (!$response) return $this->jsonErrorResponse('Failed to query M-Pesa transaction status.', 500);
        
        if (isset($response->ResultCode)) {
            if ($response->ResultCode == '0') {
                PaymentModel::update($paymentId, ['status' => 'completed']);
                if ($payment['reservation_id']) Reservation::update($payment['reservation_id'], ['status' => 'CONFIRMED']);
                return $this->jsonResponse(['status' => 'completed', 'message' => $response->ResultDesc]);
            } else {
                PaymentModel::update($paymentId, ['status' => 'failed']);
                if ($payment['reservation_id']) Reservation::update($payment['reservation_id'], ['status' => 'FAILED']);
                return $this->jsonResponse(['status' => 'failed', 'message' => $response->ResultDesc]);
            }
        }
        return $this->jsonErrorResponse('Could not determine transaction status.', 500, ['mpesaResponse' => $response]);
    }

    private function _queryStkStatus($accessToken, $checkoutRequestID) {
        $url = $this->mpesaBaseUrl . '/mpesa/stkpushquery/v1/query';
        $shortCode = Config::get('MPESA_SHORTCODE');
        $passkey = Config::get('MPESA_PASSKEY');
        $timestamp = date('YmdHis');
        $password = base64_encode($shortCode . $passkey . $timestamp);

        $payload = [
            'BusinessShortCode' => $shortCode, 'Password' => $password,
            'Timestamp' => $timestamp, 'CheckoutRequestID' => $checkoutRequestID
        ];

        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $accessToken],
                'json' => $payload
            ]);
            return json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            error_log("Guzzle error querying STK status: " . $e->getMessage());
            return null;
        }
    }
}