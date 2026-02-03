<?php
namespace App\Models;

use PDO;

class Message extends BaseModel {
    protected static $tableName = 'messages';

    public static function findByReceiverId($receiverId) {
        $sql = "
            SELECT m.*, u.name as sender_name
            FROM " . self::$tableName . " m
            JOIN users u ON m.sender_id = u.id
            WHERE m.receiver_id = ?
            ORDER BY m.created_at DESC
        ";
        return self::rawQuery($sql, [$receiverId], true);
    }
}
