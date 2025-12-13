<?php
require_once __DIR__ . '/../src/config/database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Execute the main schema file first
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $db->exec($schema);
    echo "Executed schema.sql\n";

    $migrations = glob(__DIR__ . '/migrations/*.sql');

    foreach ($migrations as $migration) {
        $sql = file_get_contents($migration);
        $db->exec($sql);
        echo "Executed migration: " . basename($migration) . "\n";
    }

    echo "All migrations successful!";
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}


