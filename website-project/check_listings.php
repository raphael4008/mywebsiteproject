<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Models\Listing;
use App\Config\DatabaseConnection;

try {
    // 1. Check DB Connection
    $pdo = DatabaseConnection::getInstance()->getConnection();
    echo "Database connected.\n";

    // 2. Check total listings count
    $stmt = $pdo->query("SELECT COUNT(*) FROM listings");
    $count = $stmt->fetchColumn();
    echo "Total listings in DB: " . $count . "\n";

    if ($count == 0) {
        echo "WARNING: No listings found in the database. Run seeders!\n";
    }

    // 3. Test Search Logic
    echo "Testing Listing::search([])...\n";
    $result = Listing::search([]);

    if (isset($result['data']) && is_array($result['data'])) {
        echo "Search returned " . count($result['data']) . " results.\n";
        if (count($result['data']) > 0) {
            echo "Sample Listing Title: " . $result['data'][0]['title'] . "\n";
        }
    }
    else {
        echo "ERROR: Search returned unexpected format.\n";
        print_r($result);
    }

}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}