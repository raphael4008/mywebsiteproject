<?php

namespace App\Middleware;

class CorsMiddleware
{
    public function handle()
    {
        // It's recommended to pull this from a config file in a real application
        $allowedOrigins = [
            'http://localhost:3000', 
            'http://127.0.0.1:3000', 
            'http://localhost:8000', // Allow self for API testing if needed
            // Add your production frontend domain here
            // 'https://www.your-production-domain.com'
        ];

        if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
        }

        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            header('Access-Control-Max-Age: 86400'); // Cache for 1 day
            http_response_code(200);
            exit();
        }
    }
}
