<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Config\DatabaseConnection;

try {
    $pdo = DatabaseConnection::getInstance()->getConnection();

    $users = [
        [
            'name' => 'Admin',
            'email' => 'admin@househunting.com',
            'password' => password_hash('admin', PASSWORD_DEFAULT),
            'role' => 'admin',
        ],
        [
            'name' => 'Owner',
            'email' => 'owner@househunting.com',
            'password' => password_hash('owner', PASSWORD_DEFAULT),
            'role' => 'owner',
        ],
    ];

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");

    foreach ($users as $user) {
        $stmt->execute($user);
    }

    echo "Default admin and owner users have been seeded successfully.\n";

} catch (PDOException $e) {
    die("Database seeding failed: " . $e->getMessage() . "\n");
}

