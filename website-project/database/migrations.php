<?php

require_once __DIR__ . '/../src/config/database.php';

$db = Database::getInstance()->getConnection();

// Create agents table
$db->exec("
    CREATE TABLE IF NOT EXISTS agents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        location VARCHAR(255) NOT NULL,
        specialization VARCHAR(255) NOT NULL,
        image VARCHAR(255) NOT NULL
    )
");

// Create neighborhoods table
$db->exec("
    CREATE TABLE IF NOT EXISTS neighborhoods (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        image VARCHAR(255) NOT NULL
    )
");

echo "Migrations created successfully!";
