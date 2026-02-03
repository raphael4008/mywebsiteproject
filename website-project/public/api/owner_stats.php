<?php
// api/owner_stats.php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/Config/DatabaseConnection.php';
require_once __DIR__ . '/../../src/Services/AuthService.php';

use App\Services\AuthService;

header('Content-Type: application/json');

try {
    $authService = new AuthService();
    $user = $authService->verifyToken();

    if (!$user || ($user['role'] !== 'owner' && $user['role'] !== 'admin')) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $ownerId = $user['id'];

    $pdo = \App\Config\DatabaseConnection::getInstance()->getConnection();

    // Get total listings for the owner
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM listings WHERE owner_id = ?');
    $stmt->execute([$ownerId]);
    $totalListings = $stmt->fetchColumn();

    // Get active (verified) listings for the owner
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM listings WHERE owner_id = ? AND verified = 1');
    $stmt->execute([$ownerId]);
    $activeListings = $stmt->fetchColumn();

    // Get total views for the owner's listings
    $stmt = $pdo->prepare('SELECT SUM(views) FROM listings WHERE owner_id = ?');
    $stmt->execute([$ownerId]);
    $totalViews = $stmt->fetchColumn();

    // Get total earnings for the owner
    // This is a simplified example. A real-world scenario would be more complex.
    $stmt = $pdo->prepare('
        SELECT SUM(p.amount) 
        FROM payments p
        JOIN reservations r ON p.reservation_id = r.id
        JOIN listings l ON r.listing_id = l.id
        WHERE l.owner_id = ? AND p.status = "completed"
    ');
    $stmt->execute([$ownerId]);
    $totalEarnings = $stmt->fetchColumn();

    echo json_encode([
        'totalListings' => (int)$totalListings,
        'activeListings' => (int)$activeListings,
        'totalViews' => (int)$totalViews,
        'totalEarnings' => (float)$totalEarnings,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch owner stats.', 'message' => $e->getMessage()]);
}
