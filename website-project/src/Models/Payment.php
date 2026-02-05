<?php
namespace App\Models;

use App\Models\BaseModel;

class Payment extends BaseModel
{
    protected static $tableName = 'payments';
    protected static $primaryKey = 'id';
    protected static $fillable = ['reservation_id', 'amount', 'payment_method', 'status', 'transaction_id', 'created_at', 'updated_at'];

    public static function findByReservationId($reservationId)
    {
        return static::where(['reservation_id' => $reservationId]);
    }

    public static function markCompleted($id, $transactionId = null)
    {
        $data = ['status' => 'completed'];
        if ($transactionId)
            $data['transaction_id'] = $transactionId;
        return parent::update($id, $data);
    }

    public static function findByTransactionId($transactionId)
    {
        $result = static::where(['transaction_id' => $transactionId]);
        return !empty($result) ? $result[0] : null;
    }

    public static function updateByReservationId($reservationId, $data)
    {
        $pdo = self::getPdo();
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $reservationId;

        $sql = "UPDATE " . static::$tableName . " SET " . implode(', ', $fields) . " WHERE reservation_id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($values);
    }
}