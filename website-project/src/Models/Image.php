<?php
namespace App\Models;

use \PDO;
use \PDOException;

class Image {
    private static $tableName = 'images';

    /**
     * Creates a new image record in the database.
     *
     * @param int $listingId The ID of the listing this image belongs to.
     * @param string $imagePath The path to the image file.
     * @param bool $isPrimary Whether this is the primary image for the listing.
     * @param int $displayOrder The display order of the image.
     * @return int|false The ID of the newly created image, or false on failure.
     */
    public static function create(int $listingId, string $imagePath, bool $isPrimary = false, int $displayOrder = 0) {
        try {
            $pdo = \Database::getInstance();
            $stmt = $pdo->prepare("INSERT INTO " . self::$tableName . " (listing_id, image_path, is_primary, display_order) VALUES (:listing_id, :image_path, :is_primary, :display_order)");
            $stmt->bindParam(':listing_id', $listingId, PDO::PARAM_INT);
            $stmt->bindParam(':image_path', $imagePath, PDO::PARAM_STR);
            $stmt->bindParam(':is_primary', $isPrimary, PDO::PARAM_BOOL);
            $stmt->bindParam(':display_order', $displayOrder, PDO::PARAM_INT);
            $stmt->execute();
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating image: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Finds images by listing ID.
     *
     * @param int $listingId The ID of the listing to find images for.
     * @return array An array of image records.
     */
    public static function findByListingId(int $listingId): array {
        try {
            $pdo = \Database::getInstance();
            $stmt = $pdo->prepare("SELECT * FROM " . self::$tableName . " WHERE listing_id = :listing_id ORDER BY display_order ASC, is_primary DESC");
            $stmt->bindParam(':listing_id', $listingId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding images by listing ID: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Deletes an image by its ID.
     *
     * @param int $imageId The ID of the image to delete.
     * @return bool True on success, false on failure.
     */
    public static function delete(int $imageId): bool {
        try {
            $pdo = \Database::getInstance();
            $stmt = $pdo->prepare("DELETE FROM " . self::$tableName . " WHERE id = :id");
            $stmt->bindParam(':id', $imageId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting image: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes all images for a given listing.
     *
     * @param int $listingId The ID of the listing whose images to delete.
     * @return bool True on success, false on failure.
     */
    public static function deleteByListingId(int $listingId): bool {
        try {
            $pdo = \Database::getInstance();
            $stmt = $pdo->prepare("DELETE FROM " . self::$tableName . " WHERE listing_id = :listing_id");
            $stmt->bindParam(':listing_id', $listingId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting images by listing ID: " . $e->getMessage());
            return false;
        }
    }
}
