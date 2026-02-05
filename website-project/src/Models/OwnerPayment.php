<?php
namespace App\Models;

use \PDO;
use App\Models\BaseModel; // Add use statement for BaseModel

class OwnerPayment extends BaseModel {
    protected static $tableName = 'payments';
    protected static $primaryKey = 'id';
    protected static $fillable = ['reservation_id', 'status', 'amount', 'payment_method', 'transaction_id', 'created_at', 'updated_at']; // Example fillable fields

    public static function findByOwnerId($ownerId) {
        $sql = "
            SELECT p.*, l.title as property_title FROM payments p
            JOIN reservations r ON p.reservation_id = r.id
            JOIN listings l ON r.listing_id = l.id
            WHERE l.owner_id = ?
            ORDER BY p.created_at DESC
        ";
        return self::rawQuery($sql, [$ownerId], true);
    }

    public static function getAll() {
        return static::rawQuery("SELECT * FROM " . static::$tableName . " ORDER BY created_at DESC", [], true, PDO::FETCH_ASSOC);
    }

    public static function updateStatus($id, $status) {
        return parent::update($id, ['status' => $status]); // Utilize BaseModel's update method
    }
}