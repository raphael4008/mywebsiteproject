<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Helpers\Router;
use App\Helpers\JwtMiddleware;
use App\Controllers\ListingController;
use App\Controllers\AuthController;
use App\Controllers\AmenityController;
use App\Controllers\TransportController;
use App\Controllers\PaymentController;
use App\Controllers\OwnerController;
use App\Config\Database;

Database::setup();

header('Content-Type: application/json');

$router = new Router();

// Auth routes
$router->add('POST', '/register', function () {
    $data = json_decode(file_get_contents('php://input'), true);
    (new AuthController())->register($data);
});

$router->add('POST', '/login', function () {
    $data = json_decode(file_get_contents('php://input'), true);
    (new AuthController())->login($data);
});

// Listing routes
$router->add('GET', '/listings/search', function () {
    (new ListingController())->search();
});

$router->add('GET', '/cities', function () {
    (new ListingController())->getCities();
});

$router->add('GET', '/listings/([0-9]+)', function ($id) {
    (new ListingController())->getById($id);
});

$router->add('POST', '/listings', function ($user) {
    $data = json_decode(file_get_contents('php://input'), true);
    (new ListingController())->create($data, $user);
}, JwtMiddleware::class);

$router->add('PUT', '/listings/([0-9]+)', function ($id, $user) {
    $data = json_decode(file_get_contents('php://input'), true);
    (new ListingController())->update($id, $data, $user);
}, JwtMiddleware::class);

$router->add('DELETE', '/listings/([0-9]+)', function ($id, $user) {
    (new ListingController())->delete($id, $user);
}, JwtMiddleware::class);

// Amenity routes
$router->add('GET', '/amenities', function () {
    (new AmenityController())->index();
});

// Transport routes
$router->add('POST', '/transport', function () {
    (new TransportController())->handleRequest();
});

// Payment routes
$router->add('POST', '/payment/create', function () {
    (new PaymentController())->createPayment();
});

$router->add('POST', '/payment/execute', function () {
    $data = json_decode(file_get_contents('php://input'), true);
    (new PaymentController())->executePayment($data);
});

// Owner routes
$router->add('GET', '/landlord/properties', function () {
    (new OwnerController())->getListings();
});

$router->add('GET', '/landlord/analytics', function () {
    (new OwnerController())->getDashboardStats();
});

$router->add('DELETE', '/landlord/properties/([0-9]+)', function ($id) {
    (new OwnerController())->deleteListing($id);
});

$router->dispatch();