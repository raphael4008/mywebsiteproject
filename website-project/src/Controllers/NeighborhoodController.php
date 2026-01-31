<?php

namespace App\Controllers;

use App\Config\DatabaseConnection;
use PDO;

class NeighborhoodController
{
    public function getNeighborhoods()
    {
        try {
            $db = DatabaseConnection::getInstance()->getConnection();

            // Return neighborhoods enriched with listing statistics
            $sql = "SELECT n.*, 
                (SELECT COUNT(*) FROM listings l WHERE l.neighborhood_id = n.id) AS listings_count,
                (SELECT ROUND(AVG(l.rent_amount)) FROM listings l WHERE l.neighborhood_id = n.id) AS avg_rent,
                (SELECT AVG(l.latitude) FROM listings l WHERE l.neighborhood_id = n.id AND l.latitude IS NOT NULL) AS avg_lat,
                (SELECT AVG(l.longitude) FROM listings l WHERE l.neighborhood_id = n.id AND l.longitude IS NOT NULL) AS avg_lng
                FROM neighborhoods n 
                ORDER BY n.name ASC";
            $stmt = $db->query($sql);
            $neighborhoods = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode($neighborhoods);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
}
