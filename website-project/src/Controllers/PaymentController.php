<?php
namespace App\Controllers;

use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Config\Config;


class PaymentController {
    private $apiContext;
    private $config;

    public function __construct() {
        $this->config = Config::getInstance();

        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                $this->config->get('PAYPAL_CLIENT_ID'),
                $this->config->get('PAYPAL_CLIENT_SECRET')
            )
        );

        $this->apiContext->setConfig([
            'mode' => $this->config->get('PAYPAL_MODE') // 'sandbox' or 'live'
        ]);

        // Set Stripe API key
        Stripe::setApiKey($this->config->get('STRIPE_SECRET_KEY'));
    }

    public function createPayment() {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setTotal('10.00'); // The amount to be charged
        $amount->setCurrency('USD');

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription('Reservation Fee');

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->config->get('PAYPAL_RETURN_URL'))
            ->setCancelUrl($this->config->get('PAYPAL_CANCEL_URL'));

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions([$transaction])
            ->setRedirectUrls($redirectUrls);

        try {
            $payment->create($this->apiContext);
            $this->jsonResponse(['id' => $payment->getId()]);
        } catch (\Exception $ex) {
            $this->jsonResponse(['error' => $ex->getMessage()], 500);
        }
    }

    public function executePayment($data) {
        if (!isset($data['paymentID']) || !isset($data['payerID'])) {
            $this->jsonResponse(['error' => 'PaymentID and PayerID are required'], 400);
            return;
        }

        $payment = Payment::get($data['paymentID'], $this->apiContext);
        $execution = new \PayPal\Api\PaymentExecution();
        $execution->setPayerId($data['payerID']);

        try {
            $result = $payment->execute($execution, $this->apiContext);
            $this->jsonResponse($result);
        } catch (\Exception $ex) {
            $this->jsonResponse(['error' => $ex->getMessage()], 500);
        }
    }

    // New method for Stripe payment processing
    public function processStripePayment($data) {
        // Here, amount should be dynamic and validated against a reservation or product ID
        // For now, using a placeholder similar to the original charge.php
        if (empty($data['payment_method_id'])) {
            $this->jsonResponse(['error' => 'Payment method ID is required'], 400);
            return;
        }

        try {
            $paymentIntent = PaymentIntent::create([
                'payment_method' => $data['payment_method_id'],
                'amount' => 1000, // Hardcoded for now, but should be dynamic and validated
                'currency' => 'usd',
                'confirmation_method' => 'manual',
                'confirm' => true,
            ]);

            $this->jsonResponse(['success' => true, 'paymentIntentId' => $paymentIntent->id]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    // New method for M-Pesa payment processing
    public function processMpesaPayment($data) {
        $phone = $data['phone'] ?? null;
        $amount = $data['amount'] ?? null; // Should be dynamic and validated

        if (!$phone || !$amount) {
            $this->jsonResponse(['error' => 'Phone number and amount are required'], 400);
            return;
        }

        // M-Pesa API credentials from .env
        $consumerKey = $this->config->get('MPESA_CONSUMER_KEY');
        $consumerSecret = $this->config->get('MPESA_CONSUMER_SECRET');
        $shortCode = $this->config->get('MPESA_SHORTCODE');
        $passkey = $this->config->get('MPESA_PASSKEY');
        $callbackUrl = $this->config->get('MPESA_CALLBACK_URL');

        // Get access token
        $accessToken = $this->getMpesaAccessToken($consumerKey, $consumerSecret);

        if (!$accessToken) {
            $this->jsonResponse(['error' => 'Failed to get M-Pesa access token'], 500);
            return;
        }

        // Initiate STK push
        $response = $this->initiateStkPush($accessToken, $shortCode, $passkey, $phone, $amount, $callbackUrl);

        if (isset($response->ResponseCode) && $response->ResponseCode == '0') {
            $this->jsonResponse(['success' => true, 'mpesaResponse' => $response]);
        } else {
            $this->jsonResponse(['error' => 'M-Pesa STK push failed', 'mpesaResponse' => $response], 500);
        }
    }

    private function getMpesaAccessToken($consumerKey, $consumerSecret) {
        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . base64_encode($consumerKey . ':' . $consumerSecret)]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($result);
        return $result->access_token ?? null;
    }

    private function initiateStkPush($accessToken, $shortCode, $passkey, $phone, $amount, $callbackUrl) {
        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $timestamp = date('YmdHis');
        $password = base64_encode($shortCode . $passkey . $timestamp);

        $payload = [
            'BusinessShortCode' => $shortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => $shortCode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => 'HouseHunting',
            'TransactionDesc' => 'Payment for house reservation'
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response);
    }
}