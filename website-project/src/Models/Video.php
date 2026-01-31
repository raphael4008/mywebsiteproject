<?php
namespace App\Models;

use App\Models\BaseModel;
use App\Config\DatabaseConnection;

class Video extends BaseModel {
    protected static $tableName = 'videos';
    protected static $fillable = ['listing_id', 'url', 'title', 'created_at'];

    public static function findByListingId(int $listingId): array {
        $pdo = DatabaseConnection::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT id, listing_id, url, title, created_at FROM videos WHERE listing_id = ? ORDER BY id ASC");
        $stmt->execute([$listingId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
