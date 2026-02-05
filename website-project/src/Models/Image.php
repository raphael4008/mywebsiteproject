<?php
namespace App\Models;

use App\Config\DatabaseConnection;
use PDO;

class Image extends BaseModel
{
    protected static $tableName = 'images';
    // Keep fillable flexible but we'll use a custom create to adapt to differing schemas
    protected static $fillable = ['listing_id', 'path', 'image_path', 'is_main', 'is_primary', 'created_at'];

    public static function findByListingId(int $listingId): array
    {
        $pdo = self::getPdo();
        // Determine available columns to avoid referencing missing ones
        $colStmt = $pdo->query("SHOW COLUMNS FROM images");
        $columns = $colStmt->fetchAll(\PDO::FETCH_COLUMN);

        if (in_array('path', $columns) && in_array('image_path', $columns)) {
            $pathExpr = "COALESCE(path, image_path) AS path";
        }
        elseif (in_array('path', $columns)) {
            $pathExpr = "path AS path";
        }
        elseif (in_array('image_path', $columns)) {
            $pathExpr = "image_path AS path";
        }
        else {
            $pathExpr = "'' AS path";
        }

        if (in_array('is_main', $columns) && in_array('is_primary', $columns)) {
            $mainExpr = "COALESCE(is_main, is_primary) AS is_main";
        }
        elseif (in_array('is_main', $columns)) {
            $mainExpr = "is_main AS is_main";
        }
        elseif (in_array('is_primary', $columns)) {
            $mainExpr = "is_primary AS is_main";
        }
        else {
            $mainExpr = "0 AS is_main";
        }

        $sql = "SELECT id, listing_id, $pathExpr, $mainExpr, created_at FROM images WHERE listing_id = ? ORDER BY id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$listingId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        // Normalize path to include images/ prefix when storing simple filenames
        foreach ($rows as &$r) {
            if (!empty($r['path'])) {
                // Fix potential malformed paths like 'a..jpg'
                $r['path'] = str_replace('..', '.', $r['path']);
                if (strpos($r['path'], '/') === false) {
                    $r['path'] = 'images/' . $r['path'];
                }
            }
        }
        return $rows;
    }

    public static function findByListingIds(array $listingIds): array
    {
        if (empty($listingIds))
            return [];
        $placeholders = implode(',', array_fill(0, count($listingIds), '?'));
        $pdo = self::getPdo();
        // Determine columns like above to avoid referencing non-existent columns
        $colStmt = $pdo->query("SHOW COLUMNS FROM images");
        $columns = $colStmt->fetchAll(\PDO::FETCH_COLUMN);

        if (in_array('path', $columns) && in_array('image_path', $columns)) {
            $pathExpr = "COALESCE(path, image_path) AS path";
        }
        elseif (in_array('path', $columns)) {
            $pathExpr = "path AS path";
        }
        elseif (in_array('image_path', $columns)) {
            $pathExpr = "image_path AS path";
        }
        else {
            $pathExpr = "'' AS path";
        }

        if (in_array('is_main', $columns) && in_array('is_primary', $columns)) {
            $mainExpr = "COALESCE(is_main, is_primary) AS is_main";
        }
        elseif (in_array('is_main', $columns)) {
            $mainExpr = "is_main AS is_main";
        }
        elseif (in_array('is_primary', $columns)) {
            $mainExpr = "is_primary AS is_main";
        }
        else {
            $mainExpr = "0 AS is_main";
        }

        $sql = "SELECT id, listing_id, $pathExpr, $mainExpr, created_at FROM images WHERE listing_id IN ($placeholders) ORDER BY listing_id, id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($listingIds);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            if (!empty($r['path'])) {
                // Fix potential malformed paths like 'a..jpg'
                $r['path'] = str_replace('..', '.', $r['path']);
                if (strpos($r['path'], '/') === false) {
                    $r['path'] = 'images/' . $r['path'];
                }
            }
        }
        return $rows;
    }

    /**
     * Custom create that adapts to either 'path' or 'image_path' column in the DB.
     */
    public static function create(array $data, ?PDO $pdo = null)
    {
        $pdo = $pdo ?? self::getPdo();

        // Determine which column exists
        $colStmt = $pdo->query("SHOW COLUMNS FROM images");
        $columns = $colStmt->fetchAll(\PDO::FETCH_COLUMN);

        $usePath = in_array('path', $columns);
        $useImagePath = in_array('image_path', $columns);

        $insertCols = ['listing_id'];
        $values = [$data['listing_id']];

        if ($usePath) {
            $insertCols[] = 'path';
            $values[] = $data['path'] ?? $data['image_path'] ?? '';
        }
        elseif ($useImagePath) {
            $insertCols[] = 'image_path';
            $values[] = $data['image_path'] ?? $data['path'] ?? '';
        }

        $placeholders = implode(',', array_fill(0, count($insertCols), '?'));
        $cols = implode(',', $insertCols);

        $sql = "INSERT INTO images ($cols) VALUES ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        return $pdo->lastInsertId();
    }

    public static function deleteByListingId(int $listingId): bool
    {
        $images = self::findByListingId($listingId);
        foreach ($images as $image) {
            parent::delete($image['id']);
        }
        return true;
    }
}