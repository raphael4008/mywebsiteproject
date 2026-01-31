<?php
namespace App\Helpers;

use App\Config\Config;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JwtMiddleware {
    public static $user;

    /**
     * Authorize request by decoding Bearer token from Authorization header.
     * Falls back to a development dummy user when no token/secret is present.
     *
     * @return array|null
     */
    public static function authorize() {
        $secret = Config::get('JWT_SECRET');

        // Try to get the Authorization header in a robust way
        $authHeader = null;
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if ($headers !== false) {
                foreach ($headers as $k => $v) {
                    if (strtolower($k) === 'authorization') {
                        $authHeader = $v;
                        break;
                    }
                }
            }
        }

        // Fallback to $_SERVER for some SAPI environments
        if (empty($authHeader)) {
            if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
                $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
            } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
        }

        // If no secret or no token provided, allow a dev fallback to minimize breakage
        if (empty($secret) || empty($authHeader)) {
            // Development fallback: return a dummy user when running locally
            $env = Config::get('APP_ENV', 'production');
            if (in_array(strtolower($env), ['local', 'development', 'dev'])) {
                self::$user = [
                    'id' => 1,
                    'role' => 'owner',
                    'email' => 'owner@example.com'
                ];
                return self::$user;
            }
            self::unauthorized('Missing authentication token');
        }

        // Extract token from header (expect "Bearer <token>")
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
        } else {
            self::unauthorized('Invalid Authorization header format');
        }

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            // The application encodes user info under 'data' key (see AuthController)
            $userData = [];
            if (isset($decoded->data)) {
                $userData = json_decode(json_encode($decoded->data), true);
            } else {
                // Fallback if token directly contains claims
                $userData = json_decode(json_encode($decoded), true);
            }

            if (empty($userData['id'])) {
                self::unauthorized('Invalid token payload');
            }

            self::$user = $userData;
            return self::$user;
        } catch (ExpiredException $e) {
            self::unauthorized('Token has expired');
        } catch (SignatureInvalidException $e) {
            self::unauthorized('Token signature invalid');
        } catch (\UnexpectedValueException $e) {
            self::unauthorized('Invalid token');
        } catch (\Exception $e) {
            error_log('JWT decode error: ' . $e->getMessage());
            self::unauthorized('Failed to validate token');
        }
    }

    public static function authorizeWithRole($role) {
        $user = self::authorize();
        if (!isset($user['role']) || strtolower($user['role']) !== strtolower($role)) {
            self::unauthorized('Insufficient permissions');
        }
        return $user;
    }

    private static function unauthorized($message) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized', 'message' => $message]);
        exit();
    }
}
