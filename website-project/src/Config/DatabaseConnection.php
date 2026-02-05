<?php

namespace App\Config;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class DatabaseConnection
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        try {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();
        }
        catch (\Dotenv\Exception\InvalidPathException $e) {
            // Log error but allow execution to proceed (env vars might be set in server config)
            error_log("Dotenv error: .env file not found.");
        }

        $host = '127.0.0.1';
        $db = $_ENV['DB_NAME'] ?? 'househunting';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        $port = $_ENV['DB_PORT'] ?? 3306;
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
        }
        catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new DatabaseConnection();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}