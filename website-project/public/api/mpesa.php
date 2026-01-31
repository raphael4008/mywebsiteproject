<?php
// public/api/mpesa.php

require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

header('Content-Type: application/json');

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json);

$phone = $data->phone ?? null;
$amount = $data->amount ?? 1; // Default to 1 KES for testing

if (!$phone) {
    echo json_encode(['error' => 'Phone number is required']);
    exit;
}

// M-Pesa API credentials from .env
$consumerKey = $_ENV['MPESA_CONSUMER_KEY'];
$consumerSecret = $_ENV['MPESA_CONSUMER_SECRET'];
$shortCode = $_ENV['MPESA_SHORTCODE'];
$passkey = $_ENV['MPESA_PASSKEY'];
$callbackUrl = $_ENV['MPESA_CALLBACK_URL'];

// Get access token
$accessToken = getMpesaAccessToken($consumerKey, $consumerSecret);

if (!$accessToken) {
    echo json_encode(['error' => 'Failed to get access token']);
    exit;
}

// Initiate STK push
$response = initiateStkPush($accessToken, $shortCode, $passkey, $phone, $amount, $callbackUrl);

echo json_encode($response);

function getMpesaAccessToken($consumerKey, $consumerSecret) {
    $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . base64_encode($consumerKey . ':' . $consumerSecret)]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);
    curl_close($curl);

    $result = json_decode($result);
    return $result->access_token ?? null;
}

function initiateStkPush($accessToken, $shortCode, $passkey, $phone, $amount, $callbackUrl) {
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
