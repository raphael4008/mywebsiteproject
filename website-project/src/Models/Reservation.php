<?php
namespace App\Models;

use \PDO;
use \Exception;
use App\Models\Listing; // For updating listing status

class Reservation extends BaseModel {
    protected static $tableName = 'reservations';
    protected static $primaryKey = 'id';
    protected static $fillable = ['listing_id', 'user_id', 'status', 'fee_paid', 'created_at', 'updated_at']; // Define fillable fields

    public static function getAll($params = []) {
        $pdo = self::getPdo();
        $sql = "SELECT r.*, l.title as listing_title, u.name as user_name, u.email as user_email FROM " . static::$tableName . " r JOIN listings l ON r.listing_id = l.id JOIN users u ON r.user_id = u.id";
        $args = [];
        $conditions = [];

        if (!empty($params['owner_id'])) {
            $conditions[] = "l.owner_id = ?";
            $args[] = $params['owner_id'];
        }

        if (!empty($params['status'])) {
            // Use case-insensitive comparison for status to tolerate mixed-case values
            $conditions[] = "LOWER(r.status) = ?";
            $args[] = strtolower($params['status']);
        }

        if (!empty($params['user_id'])) {
            $conditions[] = "r.user_id = ?";
            $args[] = $params['user_id'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY r.created_at DESC";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($args);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Log the error, and return an empty array or rethrow a custom exception
            error_log("Database error in Reservation::getAll(): " . $e->getMessage());
            return [];
        }
    }

    public static function findById($id) {
        return static::find($id); // Utilize BaseModel's find method
    }

    public static function create($data) {
        $pdo = self::getPdo();
        $pdo->beginTransaction();

        try {
            // Use BaseModel's create for the reservation (avoid recursion by calling parent)
            $reservationId = parent::create([
                'listing_id' => $data['listing_id'],
                'user_id' => $data['user_id'],
                'status' => 'PENDING', // Default status
            ], $pdo);

            // Update listing status using the Listing model to stay consistent
            Listing::update($data['listing_id'], ['status' => 'PENDING', 'is_reserved' => 1], $pdo);

            $pdo->commit();

            return $reservationId;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function updateStatus($id, $status) {
        return parent::update($id, ['status' => $status]); // Utilize BaseModel's update method
    }

    public static function updateFeePaid($id, $fee_paid) {
        return parent::update($id, ['fee_paid' => $fee_paid]); // Utilize BaseModel's update method
    }

    public static function delete($id): bool {
        return parent::delete($id); // Utilize BaseModel's delete method
    }
}