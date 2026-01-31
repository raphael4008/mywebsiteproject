<?php
namespace App\Services;

use App\Config\Config;
use App\Models\Payment;

class MpesaService {
    private $consumerKey;
    private $consumerSecret;
    private $shortCode;
    private $passkey;
    private $callbackUrl;
    private $baseUrl;

    public function __construct() {
        $this->consumerKey = Config::get('MPESA_CONSUMER_KEY');
        $this->consumerSecret = Config::get('MPESA_CONSUMER_SECRET');
        $this->shortCode = Config::get('MPESA_SHORTCODE');
        $this->passkey = Config::get('MPESA_PASSKEY');
        $this->callbackUrl = Config::get('MPESA_CALLBACK_URL');
        
        // Use sandbox URL for development, check an env var to switch to production
        $env = Config::get('MPESA_ENV', 'sandbox');
        $this->baseUrl = ($env === 'production') 
            ? 'https://api.safaricom.co.ke' 
            : 'https://sandbox.safaricom.co.ke';
    }

    /**
     * Initiates an STK Push request to the Safaricom API.
     *
     * @param string $phoneNumber The customer's phone number in 254... format.
     * @param float $amount The amount to be transacted.
     * @param string $accountReference A reference for the transaction (e.g., reservation ID).
     * @param string $transactionDesc A description of the transaction.
     * @return array The response from the Safaricom API.
     */
    public function initiateSTKPush($phoneNumber, $amount, $accountReference, $transactionDesc = 'HouseHunting Payment') {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['error' => 'Could not retrieve access token.'];
        }

        $url = $this->baseUrl . '/mpesa/stkpush/v1/processrequest';
        
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortCode . $this->passkey . $timestamp);
        
        // Format phone number to Safaricom's required format
        $partyA = '254' . ltrim($phoneNumber, '0+');

        $payload = [
            'BusinessShortCode' => $this->shortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => round($amount), // Amount must be an integer
            'PartyA' => $partyA,
            'PartyB' => $this->shortCode,
            'PhoneNumber' => $partyA,
            'CallBackURL' => $this->callbackUrl,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc
        ];

        $response = $this->makeRequest($url, $payload, $accessToken);
        
        // If the request is successful, you might want to log the CheckoutRequestID
        // to link it with the callback later.
        if (isset($response['CheckoutRequestID'])) {
            Payment::updateByReservationId($accountReference, [
                'transaction_id' => $response['CheckoutRequestID'],
                'status' => 'pending_mpesa'
            ]);
        }

        return $response;
    }

    /**
     * Handles the callback data sent by Safaricom.
     *
     * @param string $jsonData The raw JSON string from the callback.
     */
    public function handleCallback($jsonData) {
        // Log the raw callback for debugging
        file_put_contents(__DIR__ . '/../../public/data/mpesa_callbacks.log', $jsonData . PHP_EOL, FILE_APPEND);

        $data = json_decode($jsonData, true);

        if (!$data || !isset($data['Body']['stkCallback'])) {
            return; // Invalid data
        }

        $callbackData = $data['Body']['stkCallback'];
        $checkoutRequestId = $callbackData['CheckoutRequestID'];
        $resultCode = $callbackData['ResultCode'];
        
        // Find the payment record by the checkout request ID
        $payment = Payment::findByTransactionId($checkoutRequestId);
        if (!$payment) {
            // Could be a callback for a different transaction, log it and ignore.
            error_log("M-Pesa callback for unknown CheckoutRequestID: " . $checkoutRequestId);
            return;
        }

        if ($resultCode == 0) {
            // Success
            $meta = [];
            foreach ($callbackData['CallbackMetadata']['Item'] as $item) {
                $meta[$item['Name']] = $item['Value'] ?? null;
            }

            Payment::update($payment['id'], [
                'status' => 'completed',
                'mpesa_receipt_number' => $meta['MpesaReceiptNumber']
                // You can add more metadata fields to your payments table if needed
            ]);
            
            // You might also want to update the reservation status here
            // Reservation::updateStatus($payment['reservation_id'], 'confirmed');
            
        } else {
            // Failed or cancelled
            Payment::update($payment['id'], [
                'status' => 'failed_mpesa',
                'mpesa_result_desc' => $callbackData['ResultDesc']
            ]);
        }
    }

    /**
     * Retrieves an access token from the Safaricom API.
     *
     * @return string|null The access token or null on failure.
     */
    private function getAccessToken() {
        $url = $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials';
        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }

    /**
     * A helper function to make cURL requests.
     */
    private function makeRequest($url, $payload, $accessToken) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
