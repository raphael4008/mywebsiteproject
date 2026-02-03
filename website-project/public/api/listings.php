<?php
// api/listings.php

// Core entry point for listing-related API calls

// Include foundational files
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/Config/DatabaseConnection.php';
require_once __DIR__ . '/../../src/Models/Listing.php';

// Handshake Protocol: Set response header to JSON
header('Content-Type: application/json');

// Initialize the database connection
try {
    $pdoconn = \App\Config\DatabaseConnection::getInstance()->getConnection();
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed.',
        'details' => $e->getMessage() // For development only
    ]);
    exit;
}

// Get the full request URI and remove the base path and 'api/' part
$basePath = '/househunting/website-project/public/';
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH); // Use parse_url to ignore query string
$requestPath = str_replace($basePath, '', $requestPath);
$endpoint = trim(str_replace('api/listings', '', $requestPath), '/');


// Basic Endpoint Routing
switch ($endpoint) {
    case 'featured':
        handleFeaturedListings();
        break;
    
    // Example for a future endpoint: /api/listings/search?city=Nairobi
    case 'search':
        handleSearch();
        break;
        
    default:
        // Handle case where no specific endpoint is matched
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found.']);
        break;
}

/**
 * Handles the logic for fetching featured listings.
 */
function handleFeaturedListings() {
    global $pdoconn;
    
    try {
        // SQL SAFETY: Use a prepared statement to prevent SQL injection
        $stmt = $pdoconn->prepare("
            SELECT 
                l.*, 
                l.rent_amount as price,
                (SELECT GROUP_CONCAT(i.path) FROM images i WHERE i.listing_id = l.id) as image_paths
            FROM listings l
            WHERE l.is_featured = 1
            LIMIT 6
        ");
        
        $stmt->execute();
        
        $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process image paths into an array
        foreach ($listings as &$listing) {
            if ($listing['image_paths']) {
                $paths = explode(',', $listing['image_paths']);
                $listing['images'] = array_map(function($path) {
                    // PATHFINDER CHECK: Ensure the path is relative to the web root
                    $path = str_replace('..', '.', $path);
                    return ['path' => 'images/' . basename($path)];
                }, $paths);
            } else {
                $listing['images'] = [];
            }
            unset($listing['image_paths']); // Clean up
        }

        // Return a structured response
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Featured listings retrieved successfully.',
            'data' => $listings
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: Could not fetch featured listings.',
            'details' => $e->getMessage() // For development only
        ]);
    }
}

/**
 * Handles the logic for searching listings.
 */
function handleSearch() {
    global $pdoconn;
    
    try {
        // Basic query to fetch all listings
        $stmt = $pdoconn->prepare("
            SELECT 
                l.*, 
                l.rent_amount as price,
                (SELECT GROUP_CONCAT(i.path) FROM images i WHERE i.listing_id = l.id) as image_paths
            FROM listings l
        ");
        
        $stmt->execute();
        
        $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process image paths into an array
        foreach ($listings as &$listing) {
            if ($listing['image_paths']) {
                $paths = explode(',', $listing['image_paths']);
                $listing['images'] = array_map(function($path) {
                    $path = str_replace('..', '.', $path);
                    return ['path' => 'images/' . basename($path)];
                }, $paths);
            } else {
                $listing['images'] = [];
            }
            unset($listing['image_paths']); // Clean up
        }

        // Return a structured response
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Listings retrieved successfully.',
            'data' => $listings
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: Could not fetch listings.',
            'details' => $e->getMessage() // For development only
        ]);
    }
}
