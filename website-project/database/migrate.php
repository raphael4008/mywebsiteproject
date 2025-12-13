<?php

require_once __DIR__ . '/../src/config/database.php';

$db = Database::getInstance()->getConnection();

// Execute the main schema file first
$schema = file_get_contents(__DIR__ . '/schema.sql');
$db->exec($schema);
echo "Executed schema.sql\n";

$migrations = glob(__DIR__ . '/migrations/*.php');

foreach ($migrations as $migration) {
    require_once $migration;
    $className = 'CreateAgentsTable';
    $migrationInstance = new $className();
    $migrationInstance->up();
    echo "Executed migration: " . basename($migration) . "\n";
}



echo "All migrations successful!";
