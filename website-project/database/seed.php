<?php
// database/seed.php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\DatabaseConnection;

// Get the specific seeder to run from command line arguments
$seederName = $argv[1] ?? null;

if (!$seederName) {
    echo "Please specify a seeder to run. For example: php database/seed.php AdminSeeder\n";
    exit(1);
}

// Dynamically build the class name
$seederClass = 'App\\Database\\Seeders\\' . $seederName;

if (!class_exists($seederClass)) {
    echo "Seeder '$seederName' not found.\n";
    exit(1);
}

try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
    echo "Database connected successfully. Running seeder: $seederName\n";

    $pdo->exec('SET FOREIGN_KEY_CHECKS=0;');
    $seeder = new $seederClass($pdo);
    $seeder->run();
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');

    echo "$seederName ran successfully!\n";

} catch (PDOException $e) {
    die("Database seeding failed: " . $e->getMessage() . "\n");
} catch (\Exception $e) {
    die("An error occurred during seeding: " . $e->getMessage() . "\n");
}

