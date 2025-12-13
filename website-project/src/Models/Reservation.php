<?php
namespace App\Models;

use \PDO;
use Database;

class Reservation {
    public static function getAll($params = []) {
        $pdo = \Database::getInstance();
        $sql = "SELECT r.*, l.title as listing_title, u.name as user_name, u.email as user_email FROM reservations r JOIN listings l ON r.listing_id = l.id JOIN users u ON r.user_id = u.id";
        $args = [];
        $conditions = [];

        if (!empty($params['owner_id'])) {
            $conditions[] = "l.owner_id = ?";
            $args[] = $params['owner_id'];
        }

        if (!empty($params['user_id'])) {
            $conditions[] = "r.user_id = ?";
            $args[] = $params['user_id'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY r.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    public static function findById($id) {
        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $pdo = \Database::getInstance();
        $pdo->beginTransaction();

        try {
            $sql = "INSERT INTO reservations (listing_id, user_id, status) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['listing_id'],
                $data['user_id'],
                'PENDING', // Default status
            ]);
            $reservationId = $pdo->lastInsertId();

            $sql = "UPDATE listings SET status = 'PENDING', is_reserved = 1 WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['listing_id']]);

            $pdo->commit();

            return $reservationId;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function updateStatus($id, $status) {
        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }

    public static function updateFeePaid($id, $fee_paid) {
        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare("UPDATE reservations SET fee_paid = ? WHERE id = ?");
        $stmt->execute([$fee_paid, $id]);
    }

    public static function delete($id) {
        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
    }
}