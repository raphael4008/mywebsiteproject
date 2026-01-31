<?php
namespace App\Controllers;

use App\Models\Transport as TransportModel;
use App\Models\Driver;
use App\Helpers\Request;

class TransportController extends BaseController {
    
    public function getDrivers() {
        $drivers = Driver::all();
        $this->jsonResponse($drivers);
    }

    public function handleRequest() {
        $data = Request::all();
        $success = TransportModel::create($data);
        if ($success) {
            $this->jsonResponse(['message' => 'Transport request received successfully'], 201);
        } else {
            $this->jsonResponse(['message' => 'Failed to create transport request'], 500);
        }
    }

    /**
     * Estimate transport cost based on simple heuristics.
     * Expects: pickup, dropoff, truck_size
     * Returns: estimated_cost (KES), estimated_duration_minutes
     */
    public function estimate() {
        $data = Request::all();
        $pickup = trim($data['pickup'] ?? '');
        $dropoff = trim($data['dropoff'] ?? '');
        $truck = $data['truck_size'] ?? 'pickup';

        if (!$pickup || !$dropoff) {
            return $this->jsonResponse(['error' => 'Pickup and dropoff are required.'], 400);
        }

        // Simple heuristic for distance: estimate 5-15 km for intra-city moves
        // If pickup and dropoff strings are identical, distance = 2 km
        if (strtolower($pickup) === strtolower($dropoff)) {
            $distanceKm = 2;
        } else {
            // pick a pseudo-random deterministic distance based on string lengths to avoid external APIs
            $seed = abs(crc32($pickup) - crc32($dropoff));
            $distanceKm = 5 + ($seed % 11); // between 5 and 15 km
        }

        // Rates (KES)
        $base = ['pickup' => 2000, 'canter' => 4500, 'lorry' => 8000];
        $perKm = ['pickup' => 60, 'canter' => 80, 'lorry' => 120];

        $truck = in_array($truck, ['pickup','canter','lorry']) ? $truck : 'pickup';
        $estimated = $base[$truck] + intval($perKm[$truck] * $distanceKm);
        $durationMinutes = max(20, intval($distanceKm * 6));

        return $this->jsonResponse([
            'estimated_cost' => $estimated,
            'estimated_currency' => 'KES',
            'distance_km' => $distanceKm,
            'estimated_duration_minutes' => $durationMinutes,
            'truck_size' => $truck
        ]);
    }
}