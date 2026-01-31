<?php
namespace App\Models;

use \PDO;
use \Exception;
use App\Models\BaseModel; // Add use statement for BaseModel

class Transport extends BaseModel {
    protected static $tableName = 'transport_requests';
    protected static $primaryKey = 'id';
    protected static $fillable = ['name', 'phone', 'email', 'pickup_address', 'dropoff_address', 'moving_date', 'items', 'created_at', 'updated_at'];

    public static function getAll() {
        try {
            return static::rawQuery("SELECT * FROM " . static::$tableName . " ORDER BY created_at DESC", [], true, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log error
            throw new Exception("Error fetching transport requests");
        }
    }

    public static function create($data) {
        $name = htmlspecialchars($data['name']);
        $phone = htmlspecialchars($data['phone']);
        $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        $pickup_address = htmlspecialchars($data['pickup-address']);
        $dropoff_address = htmlspecialchars($data['dropoff-address']);
        $moving_date = htmlspecialchars($data['moving-date']);
        $items = htmlspecialchars($data['items']);

        if (!$email || empty($name) || empty($phone) || empty($pickup_address) || empty($dropoff_address) || empty($moving_date) || empty($items)) {
            throw new Exception("Invalid input data");
        }

        $filteredData = [
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'pickup_address' => $pickup_address,
            'dropoff_address' => $dropoff_address,
            'moving_date' => $moving_date,
            'items' => $items,
        ];

        try {
            return parent::create($filteredData);
            } catch (\Exception $e) {
            // Log error
            throw new Exception("Error creating transport request");
        }
    }
}
