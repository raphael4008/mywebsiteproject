<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

class MigrationManager {
    private $db;

    public function __construct() {
        $this->db = \App\Config\DatabaseConnection::getInstance()->getConnection();

        // Initialize the Capsule manager for Eloquent ORM based migrations
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'database'  => $_ENV['DB_NAME'] ?? 'househunting',
            'username'  => $_ENV['DB_USER'] ?? 'root',
            'password'  => $_ENV['DB_PASS'] ?? '',
            'charset'   => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    public function run() {
        $this->createMigrationsTable();

        // Execute the main schema file first, if not already executed
        if (!$this->isMigrationRun('schema.sql')) {
            $schema = file_get_contents(__DIR__ . '/schema.sql');
            $this->db->exec($schema);
            $this->logMigration('schema.sql');
            echo "Executed schema.sql\n";
        }

        $migrations = glob(__DIR__ . '/migrations/*.sql');
        $runMigrations = $this->getRunMigrations();

        foreach ($migrations as $migration) {
            $migrationName = basename($migration);

            if (in_array($migrationName, $runMigrations)) {
                continue;
            }

            $sql = file_get_contents($migration);
            if (!empty(trim($sql))) {
                $this->db->exec($sql);
                echo "Executed SQL migration: " . $migrationName . "\n";
            } else {
                echo "Skipping empty SQL migration: " . $migrationName . "\n";
            }

            $this->logMigration($migrationName);
        }

        echo "All migrations successful!";
    }

    private function createMigrationsTable() {
        $this->db->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    private function getRunMigrations() {
        $stmt = $this->db->query("SELECT migration FROM migrations");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function logMigration($migrationName) {
        $stmt = $this->db->prepare("INSERT INTO migrations (migration) VALUES (?)");
        $stmt->execute([$migrationName]);
    }
    
    private function isMigrationRun($migrationName) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
        $stmt->execute([$migrationName]);
        return $stmt->fetchColumn() > 0;
    }

    private function getClassNameFromFileName($fileName) {
        // Remove the timestamp and extension
        $className = preg_replace('/^[0-9]+_[0-9]+_[0-9]+_[0-9]+_/', '', $fileName);
        $className = str_replace('.php', '', $className);
        // Convert snake_case to CamelCase
        $className = str_replace('_', '', ucwords($className, '_'));
        return $className;
    }
}

$migrationManager = new MigrationManager();
$migrationManager->run();