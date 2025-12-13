<?php

use App\Config\Database;

class CreateAgentsTable {
    public function up() {
        $pdo = Database::getInstance();
        $pdo->exec("
            CREATE TABLE agents (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                specialization VARCHAR(255),
                location VARCHAR(255),
                image VARCHAR(255)
            )
        ");
    }

    public function down() {
        $pdo = Database::getInstance();
        $pdo->exec("DROP TABLE IF EXISTS agents");
    }
}
