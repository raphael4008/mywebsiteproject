<?php

require_once __DIR__ . '/../../vendor/autoload.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../'); $dotenv->load();

        $host = getenv('DB_HOST');
        $db   = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
        $charset = 'utf8mb4';

        if (!$host || !$db || !$user) {
            die("Please configure the database connection in your .env file.");
        }

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
             $this->conn = new \PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
             throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance(): self {
        if (self::$instance == null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function getConnection(): \PDO {
        return $this->conn;
    }
}