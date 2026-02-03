<?php
// api/neighborhoods.php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/Config/DatabaseConnection.php';

header('Content-Type: application/json');

try {
    $pdo = \App\Config\DatabaseConnection::getInstance()->getConnection();
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

try {
    $stmt = $pdo->query('SELECT id, name, city, description, image FROM neighborhoods');
    $neighborhoods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'data' => $neighborhoods
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch neighborhoods.']);
}
