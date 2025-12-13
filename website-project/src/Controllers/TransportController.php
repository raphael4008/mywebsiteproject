<?php
namespace App\Controllers;

use App\Models\Transport as TransportModel;

class TransportController {
    public function index() {
        $requests = TransportModel::getAll();
        $this->jsonResponse($requests);
    }

    public function create($data) {
        $success = TransportModel::create($data);
        if ($success) {
            $this->jsonResponse(['message' => 'Transport request received successfully'], 201);
        } else {
            $this->jsonResponse(['message' => 'Failed to create transport request'], 500);
        }
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}