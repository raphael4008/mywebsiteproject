<?php
namespace App\Models;

use \PDO;
use \Exception;
use Database;

class Review {
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
            $stmt = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // Log error
            throw new Exception("Error fetching reviews");
        }
    }

    public static function findById($id) {
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            return null;
        }
        try {
            $pdo = self::getDB();
            $stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            // Log error
            throw new Exception("Error finding review");
        }
    }

    public static function create($data) {
        $listing_id = filter_var($data['listing_id'], FILTER_VALIDATE_INT);
        $reviewer_id = filter_var($data['reviewer_id'], FILTER_VALIDATE_INT);
        $rating = filter_var($data['rating'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]]);
        $comment = htmlspecialchars($data['comment']);

        if (!$listing_id || !$reviewer_id || !$rating || empty($comment)) {
            throw new Exception("Invalid input data");
        }

        try {
            $pdo = self::getDB();
            $sql = "INSERT INTO reviews (listing_id, reviewer_id, rating, comment) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$listing_id, $reviewer_id, $rating, $comment]);
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            // Log error
            throw new Exception("Error creating review");
        }
    }

    public static function update($id, $data) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        $rating = filter_var($data['rating'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]]);
        $comment = htmlspecialchars($data['comment']);

        if (!$id || !$rating || empty($comment)) {
            throw new Exception("Invalid input data");
        }

        try {
            $pdo = self::getDB();
            $sql = "UPDATE reviews SET rating = ?, comment = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$rating, $comment, $id]);
        } catch (PDOException $e) {
            // Log error
            throw new Exception("Error updating review");
        }
    }

    public static function delete($id) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id) {
            throw new Exception("Invalid ID");
        }

        try {
            $pdo = self::getDB();
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            // Log error
            throw new Exception("Error deleting review");
        }
    }
}
