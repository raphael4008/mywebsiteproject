<?php
namespace App\Helpers;

class Request {
    public static function get($key, $default = null) {
        return self::sanitize($_GET[$key] ?? $default);
    }

    public static function post($key, $default = null) {
        return self::sanitize($_POST[$key] ?? $default);
    }

    public static function input($key, $default = null) {
        $data = json_decode(file_get_contents('php://input'), true);
        return self::sanitize($data[$key] ?? $default);
    }

    public static function all() {
        $data = json_decode(file_get_contents('php://input'), true);
        return self::sanitize($data);
    }

    public static function queryParams() {
        return self::sanitize($_GET);
    }

    private static function sanitize($value) {
        if (is_array($value)) {
            return filter_var_array($value, FILTER_SANITIZE_STRING);
        } else {
            return filter_var($value, FILTER_SANITIZE_STRING);
        }
    }
}
