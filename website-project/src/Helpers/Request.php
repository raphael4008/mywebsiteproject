<?php
namespace App\Helpers;

class Request {
    public static function method() {
        if (isset($_POST['_method'])) {
            return strtoupper($_POST['_method']);
        }
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function get($key, $default = null) {
        return self::sanitize($_GET[$key] ?? $default);
    }

    public static function post($key, $default = null) {
        return self::sanitize($_POST[$key] ?? $default);
    }

    public static function input($key, $default = null) {
        if (self::method() === 'POST' && !empty($_POST)) {
            return self::post($key, $default);
        }
        
        $rawInput = file_get_contents('php://input');
        if ($rawInput === false) {
            error_log('Failed to read php://input stream for input method.');
            return $default;
        }

        $data = json_decode($rawInput, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // It's not JSON, it could be form-urlencoded, let's try to parse it
            parse_str($rawInput, $data);
        }
        
        return self::sanitize($data[$key] ?? $default);
    }

    public static function all() {
        if (self::method() === 'POST' && !empty($_POST)) {
            return self::sanitize($_POST);
        }
        
        $rawInput = file_get_contents('php://input');
        if ($rawInput === false) {
            error_log('Failed to read php://input stream.');
            return [];
        }

        $data = json_decode($rawInput, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Not JSON, try parsing as form-urlencoded
            parse_str($rawInput, $data);
        }
        return self::sanitize($data);
    }

    public static function queryParams() {
        return self::sanitize($_GET);
    }

    private static function sanitize($value) {
        if (is_array($value)) {
            $cleaned = [];
            foreach ($value as $k => $v) {
                $cleaned[$k] = self::sanitize($v); // Recursively sanitize array values
            }
            return $cleaned;
        } elseif (is_string($value)) {
            return htmlspecialchars(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        return $value;
    }
}