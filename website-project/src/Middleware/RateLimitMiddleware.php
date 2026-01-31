<?php

namespace App\Middleware;

class RateLimitMiddleware {
    public function handle() {
        $maxRequests = 60;
        $period = 60; // seconds
        $ip = $_SERVER['REMOTE_ADDR'];
        
        $cacheDir = sys_get_temp_dir() . '/ratelimit/';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        
        $ipFile = $cacheDir . md5($ip);

        $requestData = file_exists($ipFile) ? json_decode(file_get_contents($ipFile), true) : ['time' => time(), 'count' => 0];

        if (time() - $requestData['time'] > $period) {
            $requestData = ['time' => time(), 'count' => 1];
        } else {
            $requestData['count']++;
        }

        file_put_contents($ipFile, json_encode($requestData));

        if ($requestData['count'] > $maxRequests) {
            http_response_code(429);
            echo json_encode(['status' => 'error', 'message' => 'Too Many Requests']);
            exit();
        }
    }
}