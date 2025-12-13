<?php
// Initialize test environment: use in-memory SQLite for fast unit tests
putenv('DB_DSN=sqlite::memory:');
// Ensure autoload if available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Create schema for listings table in the in-memory DB
$pdo = \Database::getInstance();
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS listings (
        id TEXT PRIMARY KEY,
        title TEXT,
        city TEXT,
        neighborhood TEXT,
            style TEXT,
        htype TEXT,
        furnished INTEGER,
        rent_amount INTEGER,
        deposit_amount INTEGER,
        amenities TEXT,
        images TEXT,
        verified INTEGER,
        status TEXT
    );"
);
