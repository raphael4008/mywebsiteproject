<?php
namespace App\Models;

use \PDO;
use \Exception;
use Database;

class Neighborhood {
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
            $stmt = $pdo->query("SELECT * FROM neighborhoods");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // Log error
            throw new Exception("Error fetching neighborhoods");
        }
    }

    public static function findById($id) {
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            return null;
        }
        try {
            $pdo = self::getDB();
            $stmt = $pdo->prepare("SELECT * FROM neighborhoods WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            // Log error
            throw new Exception("Error finding neighborhood");
        }
    }

    public static function findByName($name) {
        $name = htmlspecialchars($name);
        try {
            $pdo = self::getDB();
            $stmt = $pdo->prepare("SELECT * FROM neighborhoods WHERE name = ?");
            $stmt->execute([$name]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            // Log error
            throw new Exception("Error finding neighborhood by name");
        }
    }

    public static function create($data) {
        $name = htmlspecialchars($data['name']);
        $city = htmlspecialchars($data['city']);
        $description = htmlspecialchars($data['description']);
        $image = filter_var($data['image'], FILTER_VALIDATE_URL);

        if (empty($name) || empty($city) || empty($description) || !$image) {
            throw new Exception("Invalid input data");
        }

        try {
            $pdo = self::getDB();
            $sql = "INSERT INTO neighborhoods (name, city, description, image) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $city, $description, $image]);
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            // Log error
            throw new Exception("Error creating neighborhood");
        }
    }

    public static function update($id, $data) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        $name = htmlspecialchars($data['name']);
        $city = htmlspecialchars($data['city']);
        $description = htmlspecialchars($data['description']);
        $image = filter_var($data['image'], FILTER_VALIDATE_URL);

        if (!$id || empty($name) || empty($city) || empty($description) || !$image) {
            throw new Exception("Invalid input data");
        }

        try {
            $pdo = self::getDB();
            $sql = "UPDATE neighborhoods SET name = ?, city = ?, description = ?, image = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $city, $description, $image, $id]);
        } catch (PDOException $e) {
            // Log error
            throw new Exception("Error updating neighborhood");
        }
    }

    public static function delete($id) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id) {
            throw new Exception("Invalid ID");
        }

        try {
            $pdo = self::getDB();
            $stmt = $pdo->prepare("DELETE FROM neighborhoods WHERE id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            // Log error
            throw new Exception("Error deleting neighborhood");
        }
    }

    public static function getDistinctCities() {
        try {
            $pdo = self::getDB();
            $stmt = $pdo->query("SELECT DISTINCT city FROM neighborhoods ORDER BY city");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            // Log error
            throw new Exception("Error fetching distinct cities");
        }
    }
}
