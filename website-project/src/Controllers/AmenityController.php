<?php
namespace App\Controllers;

use App\Models\Amenity as AmenityModel;

class AmenityController {
    public function index() {
        $amenities = AmenityModel::getAll();
        $this->jsonResponse($amenities);
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}