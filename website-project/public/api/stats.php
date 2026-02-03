<?php
// api/stats.php

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
    $listingsCount = $pdo->query('SELECT count(*) FROM listings')->fetchColumn();
    $usersCount = $pdo->query('SELECT count(*) FROM users')->fetchColumn();
    $citiesCount = $pdo->query('SELECT count(DISTINCT city) FROM listings')->fetchColumn();
    $pendingListingsCount = $pdo->query('SELECT count(*) FROM listings WHERE verified = 0')->fetchColumn();

    echo json_encode([
        'listings' => (int)$listingsCount,
        'users' => (int)$usersCount,
        'cities' => (int)$citiesCount,
        'pendingListings' => (int)$pendingListingsCount,
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch stats.']);
}
