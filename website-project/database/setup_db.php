<?php
// website-project/database/setup_db.php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    die("Error: .env file not found. Please create one from .env.example.");
}

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$db   = $_ENV['DB_NAME'] ?? 'househunting';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

// Connection to MySQL server without specifying a database
$socket = '/opt/lampp/var/mysql/mysql.sock';
$dsn_server = "mysql:unix_socket=$socket;host=$host;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn_server, $user, $pass, $options);

    // Drop the database if it exists to ensure a clean slate for schema import
    $pdo->exec("DROP DATABASE IF EXISTS `$db`;");

    // Create the database
    $pdo->exec("CREATE DATABASE `$db` CHARACTER SET $charset COLLATE utf8mb4_unicode_ci;");
    
    // Now connect to the specific database
    $dsn_db = "mysql:unix_socket=$socket;host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn_db, $user, $pass, $options);
    
    echo "Database '$db' connected successfully.\n";

    // Import the schema
    $schemaPath = __DIR__ . '/schema.sql';
    if (file_exists($schemaPath)) {
        $schema = file_get_contents($schemaPath);
        if ($schema) {
            $pdo->exec($schema);
            echo "Schema imported successfully.\n";
        }
    } else {
        die("Error: schema.sql not found.\n");
    }

    echo "Database setup completed successfully!\n";

} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage() . "\n");
}