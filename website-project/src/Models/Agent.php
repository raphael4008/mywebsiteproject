<?php
namespace App\Models;

use App\Models\BaseModel;
use PDO;

class Agent extends BaseModel {
    protected static $tableName = 'agents';
    protected static $primaryKey = 'id';
    protected static $fillable = ['name', 'email', 'phone', 'specialization', 'bio', 'image', 'rating', 'review_count'];

    public static function getAgents($searchTerm = null) {
        $sql = "SELECT id, name, email, phone, specialization, bio, image, rating, review_count FROM " . static::$tableName;
        $params = [];

        if ($searchTerm) {
            $sql .= " WHERE name LIKE :searchTerm OR specialization LIKE :searchTerm";
            $params[':searchTerm'] = '%' . $searchTerm . '%';
        }

        return self::rawQuery($sql, $params, true);
    }

    public static function getByIdWithDetails($id) {
        $agent = self::find($id);

        if (!$agent) {
            return null;
        }

        $listingsSql = "SELECT id, title, city, rent_amount, image_path as image FROM listings WHERE agent_id = :agentId ORDER BY created_at DESC LIMIT 6";
        $agent['listings'] = self::rawQuery($listingsSql, [':agentId' => $id], true);
        
        return $agent;
    }
}