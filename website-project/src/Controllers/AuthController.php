<?php
namespace App\Controllers;

use App\Models\User as UserModel;
use \Firebase\JWT\JWT;
use App\Config\Config;

use App\Controllers\BaseController;

use R;
class AuthController extends BaseController {
    private $jwtSecret;

    public function __construct()
    {
        $this->jwtSecret = Config::getInstance()->get('JWT_SECRET');
    }
    public function register($data) {
        if (!$this->validateRegistration($data)) {
            return;
        }

        $existingUser = R::findOne('users', 'email = ?', [$data['email']]);
        if ($existingUser) {
            $this->jsonResponse(['error' => 'Email already exists'], 409);
            return;
        }

        $user = R::dispense('users');
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = password_hash($data['password'], PASSWORD_DEFAULT);
        $user->role = 'user';
        $user->has_paid = false;
        
        $id = R::store($user);

        if ($id) {
            $this->jsonResponse(['message' => 'User registered successfully'], 201);
        } else {
            $this->jsonResponse(['error' => 'Failed to register user'], 500);
        }
    }

    public function login($data) {
        if (empty($data['email']) || empty($data['password'])) {
            $this->jsonResponse(['error' => 'Email and password are required'], 400);
            return;
        }

        $user = R::findOne('users', 'email = ?', [$data['email']]);
        if (!$user || !password_verify($data['password'], $user->password)) {
            $this->jsonResponse(['error' => 'Invalid credentials'], 401);
            return;
        }

        $payload = [
            'iat' => time(),
            'exp' => time() + (60 * 60), // 1 hour expiration
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]
        ];

        $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');
        $this->jsonResponse(['token' => $jwt]);
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
            $this->jsonResponse(['errors' => $errors], 400);
            return false;
        }

        return true;
    }
}