<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Config\DatabaseConnection;

try {
    $pdo = DatabaseConnection::getInstance()->getConnection();

    $email = 'admin@househunting.co.ke';
    $password = 'password123';

    echo "Checking user: $email\n";

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "User found!\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Role: " . $user['role'] . "\n";

        if (password_verify($password, $user['password'])) {
            echo "Password verification: SUCCESS\n";
        }
        else {
            echo "Password verification: FAILED\n";
            echo "Hash in DB: " . $user['password'] . "\n";
            echo "New Hash for '$password': " . password_hash($password, PASSWORD_DEFAULT) . "\n";
        }
    }
    else {
        echo "User NOT found.\n";

        // Attempt to create if not found
        echo "Attempting to create default admin user...\n";
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, has_paid) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            'Admin User',
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            'admin',
            1
        ]);

        if ($result) {
            echo "User created successfully.\n";
        }
        else {
            echo "Failed to create user.\n";
        }
    }

}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}