<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Listing;
use App\Models\User;

class HomeController extends BaseController
{
    /**
     * Returns a simple welcome message for the API root.
     */
    public function index()
    {
        $this->jsonResponse([
            'status' => 'success',
            'message' => 'Welcome to the HouseHunting API'
        ]);
    }

    /**
     * Returns key statistics for the platform.
     */
    public function getStats()
    {
        // Leveraging the Models for data fetching would be even better,
        // but for now, we can use the PDO instance from BaseController.
        $listingsCount = $this->pdo->query("SELECT COUNT(*) FROM listings")->fetchColumn();
        $citiesCount = $this->pdo->query("SELECT COUNT(DISTINCT city) FROM listings")->fetchColumn();
        $userCount = $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

        $stats = [
            'listings' => (int) $listingsCount,
            'cities' => (int) $citiesCount,
            'happy_families' => (int) $userCount // Renamed for clarity
        ];
        
        $this->jsonResponse([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}

