<?php
namespace App\Helpers;

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class JwtMiddleware {
    private const JWT_SECRET = $_ENV['JWT_SECRET'];

    public static function authorize() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            self::unauthorized('Authorization header not found');
        }

        $authHeader = $headers['Authorization'];
        list($jwt) = sscanf($authHeader, 'Bearer %s');

        if (!$jwt) {
            self::unauthorized('Invalid token format');
        }

        try {
            $decoded = JWT::decode($jwt, new Key(self::JWT_SECRET, 'HS256'));
            // You can add more checks here, e.g., check user roles
            return (array) $decoded->data;
        } catch (\Exception $e) {
            self::unauthorized($e->getMessage());
        }
    }

    private static function unauthorized($message) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized', 'message' => $message]);
        exit();
    }
}
