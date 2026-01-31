<?php
// tests/bootstrap.php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/../vendor/autoload.php';

// Initialize Eloquent ORM for testing
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'sqlite',
    'database'  => ':memory:',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Now that Eloquent is booted, we can get the PDO instance from it
$pdo = $capsule->getDatabaseManager()->getPdo();

// --- Schema Conversion from MySQL to SQLite ---
$schemaPath = __DIR__ . '/../database/schema.sql';
if (!file_exists($schemaPath)) {
    die("Schema file not found at {$schemaPath}");
}
$mysqlSchema = file_get_contents($schemaPath);

// 1. Remove comments and database selection
$sqliteSchema = preg_replace('/--.*$/m', '', $mysqlSchema);
$sqliteSchema = preg_replace('/USE `?\w+`?;/i', '', $sqliteSchema);
$sqliteSchema = preg_replace('/CREATE DATABASE .*;/i', '', $sqliteSchema);

// 2. Convert MySQL-specific syntax to SQLite compatible syntax
// INT AUTO_INCREMENT PRIMARY KEY -> INTEGER PRIMARY KEY AUTOINCREMENT
$sqliteSchema = preg_replace('/INT(\(.*\))?\s+AUTO_INCREMENT\s+PRIMARY\s+KEY/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sqliteSchema);
// ENUM -> TEXT
$sqliteSchema = preg_replace('/ENUM\(.*\)/i', 'TEXT', $sqliteSchema);
// TIMESTAMP DEFAULT CURRENT_TIMESTAMP -> DATETIME DEFAULT CURRENT_TIMESTAMP
$sqliteSchema = preg_replace('/TIMESTAMP/i', 'DATETIME', $sqliteSchema);
// ON UPDATE CURRENT_TIMESTAMP
$sqliteSchema = preg_replace('/ON UPDATE CURRENT_TIMESTAMP/i', '', $sqliteSchema);
// DECIMAL -> REAL
$sqliteSchema = preg_replace('/DECIMAL\(.*\)/i', 'REAL', $sqliteSchema);
// BOOLEAN -> INTEGER
$sqliteSchema = preg_replace('/BOOLEAN/i', 'INTEGER', $sqliteSchema);
// VARCHAR -> TEXT, INT -> INTEGER for broader compatibility
$sqliteSchema = preg_replace('/VARCHAR\(.*\)/i', 'TEXT', $sqliteSchema);
$sqliteSchema = preg_replace('/\bINT\b/i', 'INTEGER', $sqliteSchema);
// Remove engine and default charset
$sqliteSchema = preg_replace('/ENGINE=InnoDB\s*DEFAULT\s*CHARSET=utf8mb4\s*COLLATE=utf8mb4_unicode_ci;/i', ';', $sqliteSchema);
// Remove unique key definitions that are not standard
$sqliteSchema = preg_replace('/UNIQUE KEY `?\w+`? \((.*)\)/i', 'UNIQUE ($1)', $sqliteSchema);

// Remove foreign key constraints for simplicity in testing, or handle them carefully
// For this case, we'll keep them as they are generally compatible.

// Execute the converted schema
$pdo->exec($sqliteSchema);


// You can also seed some initial data for testing if needed
// Note: Ensure seeded data is compatible with the new schema
$pdo->exec("
    INSERT INTO users (id, name, email, password, role) VALUES 
    (1, 'Test User', 'test@example.com', 'password', 'owner'),
    (2, 'Test Renter', 'renter@example.com', 'password', 'user');

    INSERT INTO neighborhoods (id, name, city) VALUES 
    (1, 'Kilimani', 'Nairobi');
    
    INSERT INTO house_types (name) VALUES ('Apartment'), ('Studio');
    INSERT INTO styles (name) VALUES ('Modern'), ('Vintage');
");