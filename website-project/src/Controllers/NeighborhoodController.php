<?php

class NeighborhoodController
{
    public function getNeighborhoods()
    {
        require_once __DIR__ . '/../config/database.php';
        $db = Database::getInstance()->getConnection();

        $stmt = $db->query("SELECT * FROM neighborhoods");
        $neighborhoods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($neighborhoods);
    }
}
