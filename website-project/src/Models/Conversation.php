<?php
namespace App\Models;

use \PDO;
use \Exception;

class Conversation extends BaseModel {
    protected static $tableName = 'messages';
    protected static $fillable = ['sender_id', 'receiver_id', 'listing_id', 'message', 'is_read', 'created_at'];

    public static function getConversationsForUser($userId) {
        // Group messages by conversation partners
        // We want distinct pairs of users.
        $sql = "
            SELECT 
                LEAST(sender_id, receiver_id) as user1_id,
                GREATEST(sender_id, receiver_id) as user2_id,
                MAX(created_at) as last_message_time
            FROM messages
            WHERE sender_id = ? OR receiver_id = ?
            GROUP BY LEAST(sender_id, receiver_id), GREATEST(sender_id, receiver_id)
            ORDER BY last_message_time DESC
        ";
        
        return self::rawQuery($sql, [$userId, $userId], true);
    }

    public static function getMessagesBetween($userId1, $userId2) {
        $sql = "
            SELECT * FROM messages 
            WHERE (sender_id = ? AND receiver_id = ?) 
               OR (sender_id = ? AND receiver_id = ?)
            ORDER BY created_at ASC
        ";
        return self::rawQuery($sql, [$userId1, $userId2, $userId2, $userId1], true);
    }

    public static function createMessage($data) {
       // BaseModel::create will handle it if we pass correct keys
       // Ensure created_at is set if not provided (though DB default handles it, BaseModel might not rely on DB default if filtered)
       // BaseModel::create builds INSERT statement. If we don't pass created_at, it won't be in columns, so DB default takes over. Correct.
       
       return parent::create($data); 
    }
}