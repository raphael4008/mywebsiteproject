<?php
/**
 * Main application entry point and router.
 * This file handles both web view and API requests.
 */

// Start output buffering to capture any premature output (warnings/notices)
ob_start();

// Start the session to manage user login state
session_start();

// Include the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Helpers/renders.php';

// Handle execution in a subdirectory
$basePath = dirname($_SERVER['SCRIPT_NAME']);
$GLOBALS['basePath'] = $basePath;
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// If the request URI starts with the base path, remove it
if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// The Bramus router uses the original REQUEST_URI, so we have to overwrite it.
// This is a workaround for routers that don't have a setBasePath() method.
$_SERVER['REQUEST_URI'] = $requestUri ?: '/';


use Bramus\Router\Router;
use App\Middleware\CorsMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Helpers\JwtMiddleware;

try {
    // Create Router instance
    $router = new Router();

    // --- Web View Routes ---
    $router->get('/', function() {
    echo \App\Helpers\render('home', ['title' => 'HouseHunter - Find Your Dream Home']);
});
    $router->get('/listings', function() { require __DIR__ . '/listings.php'; });
    $router->get('/listing/(\d+)', function($id) {
        $_GET['id'] = $id;
        require __DIR__ . '/listing-details.php';
    });
    $router->get('/features', function() { require __DIR__ . '/features.php'; });
    $router->get('/compare', function() { require __DIR__ . '/compare.php'; });
    $router->get('/neighborhoods', function() { require __DIR__ . '/neighborhood.php'; });
    $router->get('/contact', function() { require __DIR__ . '/contact.php'; });
    $router->get('/about', function() { require __DIR__ . '/about.php'; });
    $router->get('/login', function() { require __DIR__ . '/login.php'; });
    $router->get('/register', function() { require __DIR__ . '/register.php'; });
    $router->get('/forgot-password', function() { require __DIR__ . '/forgot-password.php'; });
    $router->get('/logout', function() {
        session_destroy();
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') {
            $basePath = '';
        }
        header('Location: ' . $basePath . '/login');
        exit();
    });
    $router->get('/profile', function() { require __DIR__ . '/profile.php'; });
    $router->get('/my-reservations', function() { require __DIR__ . '/profile.php'; });
    $router->get('/saved-searches', function() { require __DIR__ . '/profile.php'; });
    $router->get('/favorites', function() { require __DIR__ . '/profile.php'; });
    $router->get('/owner/dashboard', function() { require __DIR__ . '/owners/index.php'; });
    $router->get('/owner/my-listings', function() { require __DIR__ . '/owners/index.php'; });
    $router->get('/owner/add-listing', function() { require __DIR__ . '/owners/index.php'; });
    $router->get('/owner/bookings', function() { require __DIR__ . '/owners/index.php'; });
    $router->get('/admin/dashboard', function() { require __DIR__ . '/admin/index.php'; });
    $router->get('/admin/users', function() { require __DIR__ . '/admin/index.php'; });
    $router->get('/admin/approve-listings', function() { require __DIR__ . '/admin/index.php'; });


    // --- API Routes ---
    $router->mount('/api', function() use ($router) {
        // Apply Global Middlewares for API
        (new CorsMiddleware())->handle();
        (new RateLimitMiddleware())->handle();

        // Handle _method override for forms
        if (isset($_POST['_method'])) {
            $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_method']);
        }

        // Public Routes
        $router->get('/', 'App\Controllers\HomeController@index');
        $router->get('/stats', 'App\Controllers\HomeController@getStats');
        $router->post('/register', 'App\Controllers\AuthController@register');
        $router->post('/login', 'App\Controllers\AuthController@login');
        $router->post('/contact', 'App\Controllers\ContactController@handleContactForm');
        $router->post('/auth/forgot-password', 'App\Controllers\AuthController@forgotPassword');
        $router->post('/auth/reset-password', 'App\Controllers\AuthController@resetPassword');
        $router->post('/auth/refresh', 'App\Controllers\AuthController@refresh');
        $router->get('/listings/search', 'App\Controllers\ListingController@search');
        $router->get('/listings/featured', 'App\Controllers\ListingController@getFeatured');
        $router->get('/cities', 'App\Controllers\ListingController@getCities');
        $router->get('/house-types', 'App\Controllers\ListingController@getHouseTypes');
        $router->get('/listings/([0-9]+)', 'App\Controllers\ListingController@getById');
        $router->get('/listings/([0-9]+)/availability', 'App\Controllers\ListingController@getAvailability');
        $router->get('/reviews', 'App\Controllers\ReviewController@getReviews');
        $router->get('/agents', 'App\Controllers\AgentController@getAgents');
        $router->get('/agents/([0-9]+)', 'App\Controllers\AgentController@getById');
        $router->get('/neighborhoods', 'App\Controllers\NeighborhoodController@getNeighborhoods');
        $router->get('/amenities', 'App\Controllers\AmenityController@index');
        $router->post('/messages', 'App\Controllers\MessageController@create');
        $router->post('/payment/mpesa/callback', 'App\Controllers\PaymentController@handleMpesaCallback');
        $router->post('/ai-search', 'App\Controllers\ListingController@aiSearch');

        // Authenticated User Routes
        $router->before('GET|POST|PUT|DELETE', '/users/.*', function() { JwtMiddleware::authorize(); });
        $router->mount('/users', function() use ($router) {
            $router->get('/me', 'App\Controllers\UserController@getMe');
            $router->put('/me', 'App\Controllers\UserController@updateMe');
            $router->get('/me/favorites', 'App\Controllers\UserController@getFavorites');
            $router->put('/me/favorites/([0-9]+)', 'App\Controllers\UserController@addFavorite');
            $router->delete('/me/favorites/([0-9]+)', 'App\Controllers\UserController@removeFavorite');
            $router->get('/me/searches', 'App\Controllers\UserController@getSavedSearches');
            $router->get('/me/reservations', 'App\Controllers\UserController@getReservations');
            $router->post('/ai-chat', 'App\Controllers\AIController@handleChat');
            $router->post('/transport', 'App\Controllers\TransportController@handleRequest');
            $router->post('/transport/estimate', 'App\Controllers\TransportController@estimate');
            $router->get('/drivers', 'App\Controllers\TransportController@getDrivers');
            $router->post('/listings', 'App\Controllers\ListingController@create');
            $router->put('/listings/([0-9]+)', 'App\Controllers\ListingController@updateListing');
            $router->delete('/listings/([0-9]+)', 'App\Controllers\ListingController@delete');
            $router->post('/listings/report', 'App\Controllers\ListingController@report');
            $router->post('/payment/create', 'App\Controllers\PaymentController@createPayment');
            $router->post('/payment/execute', 'App\Controllers\PaymentController@executePayment');
            $router->post('/payment/stripe/create_intent', 'App\Controllers\PaymentController@createStripePaymentIntent');
            $router->post('/payment/stripe/confirm', 'App\Controllers\PaymentController@confirmStripePayment');
            $router->post('/payment/mpesa/stk_push', 'App\Controllers\PaymentController@processMpesaPayment');
            $router->post('/reservations', 'App\Controllers\ReservationController@create');
            $router->post('/agreements', 'App\Controllers\AgreementController@create');
            $router->post('/reviews', 'App\Controllers\ReviewController@create');
        });

        // Admin Routes
        // $router->before('GET|POST|PUT|DELETE', '/admin/.*', function() { JwtMiddleware::authorizeWithRole('admin'); });
        $router->mount('/admin', function() use ($router) {
            $router->get('/system-stats', 'App\Controllers\AdminController@getSystemStats');
            $router->get('/stats', 'App\Controllers\AdminController@getStats');
            $router->post('/listings/verify/([0-9]+)', 'App\Controllers\AdminController@verifyListing');
            $router->get('/listings', 'App\Controllers\AdminController@getListings');
            $router->delete('/listings/([0-9]+)', 'App\Controllers\AdminController@deleteListing');
            $router->get('/users', 'App\Controllers\AdminController@getUsers');
            $router->post('/users/([0-9]+)/role', 'App\Controllers\AdminController@updateUserRole');
            $router->delete('/users/([0-9]+)', 'App\Controllers\AdminController@deleteUser');
            $router->get('/reservations', 'App\Controllers\AdminController@getReservations');
            $router->post('/reservations/confirm/([0-9]+)', 'App\Controllers\AdminController@confirmReservation');
            $router->post('/reservations/cancel/([0-9]+)', 'App\Controllers\AdminController@cancelReservation');
            $router->get('/payments', 'App\Controllers\AdminController@getPayments');
            $router->get('/amenities', 'App\Controllers\AmenityController@index');
            $router->post('/amenities', 'App\Controllers\AmenityController@create');
            $router->delete('/amenities/([0-9]+)', 'App\Controllers\AmenityController@delete');
        });

        // Owner Routes
        // $router->before('GET|POST|PUT|DELETE', '/owner/.*', function() { JwtMiddleware::authorize(); });
        $router->mount('/owner', function() use ($router) {
            $router->get('/stats/stream', 'App\Controllers\OwnerController@streamDashboardStats');
            $router->get('/stats', 'App\Controllers\OwnerController@getDashboardStats');
            $router->get('/my-listings', 'App\Controllers\OwnerController@getMyListings');
            $router->get('/listings', 'App\Controllers\OwnerController@getListings');
            $router->get('/messages', 'App\Controllers\OwnerController@getMessages');
            $router->delete('/listings/([0-9]+)', 'App\Controllers\OwnerController@deleteListing');
            $router->get('/profile', 'App\Controllers\OwnerController@getProfile');
            $router->put('/profile', 'App\Controllers\OwnerController@updateProfile');
            $router->get('/reservations', 'App\Controllers\OwnerController@getReservations');
            $router->get('/activities', 'App\Controllers\OwnerController@getActivities');
            $router->get('/payments', 'App\Controllers\OwnerController@getPayments');
            $router->post('/reservations/cancel/([0-9]+)', 'App\Controllers\OwnerController@cancelReservation');
            $router->get('/financials', 'App\Controllers\OwnerController@getFinancials');
            $router->get('/transactions', 'App\Controllers\OwnerController@getTransactions');
            $router->get('/listings/([0-9]+)/unavailability', 'App\Controllers\OwnerController@getUnavailability');
            $router->post('/listings/([0-9]+)/unavailability', 'App\Controllers\OwnerController@addUnavailability');
            $router->delete('/unavailability/([0-9]+)', 'App\Controllers\OwnerController@deleteUnavailability');
        });
    });

    // --- 404 Handler ---
    $router->set404(function() {
        $isApiRequest = strpos($_SERVER['REQUEST_URI'], '/api/') === 0;
        if ($isApiRequest) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['error' => 'Not Found', 'message' => 'The requested API endpoint does not exist.']);
        } else {
            http_response_code(404);
            require __DIR__ . '/404.php';
        }
    });

    // Run the router
    $router->run();

} catch (\Throwable $e) {
    // Global Error Handler for API
    while (ob_get_level()) ob_end_clean(); // Clear buffer to ensure valid JSON

    $isApiRequest = strpos($_SERVER['REQUEST_URI'], '/api/') === 0;
    http_response_code(500);

    if ($isApiRequest) {
        header('Content-Type: application/json');
        $errorResponse = ['error' => 'Internal Server Error'];
        // For development: add more detail. In production, log this securely.
        if (getenv('APP_ENV') === 'development') {
            $errorResponse['message'] = $e->getMessage();
            $errorResponse['trace'] = $e->getTrace();
        }
        echo json_encode($errorResponse);
    } else {
        // Render a user-friendly error page for web requests
        echo "<h1>500 - Internal Server Error</h1>";
        if (getenv('APP_ENV') === 'development') {
            echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
    }
}
