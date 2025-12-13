<?php
namespace App\Models;

use \PDO;
use \Exception;
use Database;

class Transport {
    private static function getDB() {
        try {
            return \Database::getInstance();
        } catch (PDOException $e) {
            // In a real application, you would log this error
            throw new Exception("Database connection failed");
        }
    }

    public static function getAll() {
        try {
            $pdo = self::getDB();
            $stmt = $pdo->query("SELECT * FROM transport_requests ORDER BY created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        try {
            $pdo = self::getDB();
            $stmt = $pdo->prepare(
                "INSERT INTO transport_requests (name, phone, email, pickup_address, dropoff_address, moving_date, items) 
                VALUES (:name, :phone, :email, :pickup_address, :dropoff_address, :moving_date, :items)"
            );
            
            return $stmt->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':email' => $email,
                ':pickup_address' => $pickup_address,
                ':dropoff_address' => $dropoff_address,
                ':moving_date' => $moving_date,
                ':items' => $items,
            ]);
        } catch (PDOException $e) {
            // Log error
            throw new Exception("Error creating transport request");
        }
    }
}
