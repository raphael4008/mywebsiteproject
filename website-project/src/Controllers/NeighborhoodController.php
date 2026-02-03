<?php

namespace App\Controllers;

use App\Config\DatabaseConnection;
use PDO;

class NeighborhoodController extends BaseController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = DatabaseConnection::getInstance()->getConnection();
    }

    public function getNeighborhoods()
    {
        try {
            // Return neighborhoods enriched with listing statistics
            $sql = "SELECT n.*, 
                (SELECT COUNT(*) FROM listings l WHERE l.neighborhood_id = n.id) AS listings_count,
                (SELECT ROUND(AVG(l.rent_amount)) FROM listings l WHERE l.neighborhood_id = n.id) AS avg_rent,
                (SELECT AVG(l.latitude) FROM listings l WHERE l.neighborhood_id = n.id AND l.latitude IS NOT NULL) AS avg_lat,
                (SELECT AVG(l.longitude) FROM listings l WHERE l.neighborhood_id = n.id AND l.longitude IS NOT NULL) AS avg_lng
                FROM neighborhoods n 
                ORDER BY n.name ASC";
            $stmt = $this->pdo->query($sql);
            $neighborhoods = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse(['data' => $neighborhoods]);
        } catch (\PDOException $e) {
            $this->jsonErrorResponse('Database error: ' . $e->getMessage(), 500);
        }
    }
}
