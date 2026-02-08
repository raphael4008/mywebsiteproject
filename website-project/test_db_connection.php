<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env file
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    // Handle the case where .env file is not found
    echo "Could not find .env file: " . $e->getMessage() . "\n";
    // You might want to exit here or provide default values
}

// Database connection parameters from environment variables
$db_host = $_ENV['DB_HOST'] ?? 'localhost';
$db_name = $_ENV['DB_NAME'] ?? 'househunting';
$db_user = $_ENV['DB_USER'] ?? 'root';
$db_pass = $_ENV['DB_PASS'] ?? '';

// Create a new MySQLi object
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check for connection errors
if ($mysqli->connect_error) {
    echo "Connection failed: " . $mysqli->connect_error . "\n";
} else {
    echo "Database connection successful!\n";
    
    // Optional: Query to check if the 'users' table exists and has data
    $query = "SELECT COUNT(*) as count FROM users";
    $result = $mysqli->query($query);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Number of users in the database: " . $row['count'] . "\n";
    } else {
        echo "Query failed: " . $mysqli->error . "\n";
    }
    
    // Close the connection
    $mysqli->close();
}

?>