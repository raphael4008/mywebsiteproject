<?php
namespace App\Models;

use \PDO;
use \PDOException;

class Amenity {
    private static $tableName = 'amenities';

    public static function getAll() {
        try {
            $pdo = \Database::getInstance();
            $stmt = $pdo->prepare("SELECT * FROM " . self::$tableName . " ORDER BY name");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
}