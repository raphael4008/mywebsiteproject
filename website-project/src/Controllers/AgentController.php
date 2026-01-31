<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Agent;
use App\Helpers\Request;

class AgentController extends BaseController
{
    public function __construct()
    {
        // No need to instantiate PDO here anymore, model handles it.
    }

    public function getAgents()
    {
        $searchTerm = Request::get('q');
        
        try {
            $agents = Agent::getAgents($searchTerm);
            $this->jsonResponse($agents);
        } catch (\PDOException $e) {
            $this->jsonErrorResponse('Database error: ' . $e->getMessage(), 500);
        }
    }

    public function getById($id)
    {
        try {
            $agent = Agent::getByIdWithDetails($id);

            if (!$agent) {
                $this->jsonErrorResponse('Agent not found', 404);
                return;
            }

            $this->jsonResponse($agent);
        } catch (\PDOException $e) {
            $this->jsonErrorResponse('Database error: ' . $e->getMessage(), 500);
        }
    }
}