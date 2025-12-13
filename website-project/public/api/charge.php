<?php
// public/api/charge.php

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use Stripe\Stripe;
use Stripe\PaymentIntent;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

header('Content-Type: application/json');

$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str);

$payment_method_id = $json_obj->payment_method_id;

try {
    $paymentIntent = PaymentIntent::create([
        'payment_method' => $payment_method_id,
        'amount' => 1000, // Amount in cents
        'currency' => 'usd',
        'confirmation_method' => 'manual',
        'confirm' => true,
    ]);

    echo json_encode(['success' => true]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
