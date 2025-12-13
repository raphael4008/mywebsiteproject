<?php

namespace App\Helpers;

class Router {
    private $routes = [];

    public function add($method, $path, $callback, $middleware = null) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback,
            'middleware' => $middleware
        ];
    }

    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $url = str_replace('/api', '', $url);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method) {
                $pattern = $this->getPatternFromPath($route['path']);
                if (preg_match($pattern, $url, $matches)) {
                    array_shift($matches);
                    
                    if ($route['middleware']) {
                        $middleware = $route['middleware'];
                        $userData = $middleware::authorize();
                        $matches['user'] = $userData;
                    }

                    $callback = $route['callback'];
                    if (is_callable($callback)) {
                        call_user_func_array($callback, $matches);
                        return;
                    }
                }
            }
        }

        // Handle not found
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }

    private function getPatternFromPath($path) {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_]+)', $path);
        return '#^' . $pattern . '$#';
    }
}
