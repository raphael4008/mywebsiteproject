<?php
namespace App\Models;

use \PDO;
use App\Models\BaseModel; // Add use statement for BaseModel

class OwnerPayment extends BaseModel {
    protected static $tableName = 'owner_payments';
    protected static $primaryKey = 'id';
    protected static $fillable = ['owner_id', 'status', 'amount', 'transaction_id', 'payment_date', 'created_at', 'updated_at']; // Example fillable fields

    public static function findByOwnerId($ownerId) {
        // Using BaseModel's where method for simplicity, adding order by
        return static::where(['owner_id' => $ownerId], 'created_at', 'DESC');
    }

    public static function getAll() {
        return static::rawQuery("SELECT * FROM " . static::$tableName . " ORDER BY created_at DESC", [], true, PDO::FETCH_ASSOC);
    }

    public static function updateStatus($id, $status) {
        return parent::update($id, ['status' => $status]); // Utilize BaseModel's update method
    }
}