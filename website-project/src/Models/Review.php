<?php
namespace App\Models;

use \PDO;
use \Exception;
use App\Models\BaseModel; // Add use statement for BaseModel

class Review extends BaseModel {
    protected static $tableName = 'reviews';
    protected static $primaryKey = 'id';
    protected static $fillable = ['listing_id', 'reviewer_id', 'rating', 'comment'];

    public static function getAll() {
        try {
            return static::rawQuery("SELECT * FROM " . static::$tableName . " ORDER BY created_at DESC", [], true, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log error
            throw new Exception("Error fetching reviews");
        }
    }

    public static function findById($id) {
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            return null;
        }
        return static::find($id);
    }

    public static function findByListingId($listingId) {
        $sql = "
            SELECT r.id, r.rating, r.comment, r.created_at, u.name as reviewer_name
            FROM " . static::$tableName . " r
            JOIN users u ON r.reviewer_id = u.id
            WHERE r.listing_id = ?
            ORDER BY r.created_at DESC
        ";
        return self::rawQuery($sql, [$listingId], true);
    }

    public static function create($data) {
        $listing_id = filter_var($data['listing_id'], FILTER_VALIDATE_INT);
        $reviewer_id = filter_var($data['reviewer_id'], FILTER_VALIDATE_INT);
        $rating = filter_var($data['rating'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]]);
        $comment = htmlspecialchars($data['comment']);

        if (!$listing_id || !$reviewer_id || !$rating || empty($comment)) {
            throw new Exception("Invalid input data");
        }

        $filteredData = [
            'listing_id' => $listing_id,
            'reviewer_id' => $reviewer_id,
            'rating' => $rating,
            'comment' => $comment,
        ];

        try {
            return parent::create($filteredData);
        } catch (\Exception $e) {
            // Log error
            throw new Exception("Error creating review");
        }
    }

    public static function update($id, $data) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        $rating = filter_var($data['rating'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]]);
        $comment = htmlspecialchars($data['comment']);

        if (!$id || !$rating || empty($comment)) {
            throw new Exception("Invalid input data");
        }

        $filteredData = [
            'rating' => $rating,
            'comment' => $comment,
        ];

        try {
            return parent::update($id, $filteredData);
        } catch (\Exception $e) {
            // Log error
            throw new Exception("Error updating review");
        }
    }

    public static function delete($id) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id) {
            throw new Exception("Invalid ID");
        }
        return parent::delete($id);
    }
}
