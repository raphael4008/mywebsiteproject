<?php

class AgentController
{
    public function getAgents()
    {
        require_once __DIR__ . '/../config/database.php';
        $db = Database::getInstance()->getConnection();

        $stmt = $db->query("SELECT * FROM agents");
        $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($agents);
    }
}
