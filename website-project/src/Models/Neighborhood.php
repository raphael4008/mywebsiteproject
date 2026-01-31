<?php
namespace App\Models;

class Neighborhood extends BaseModel {
    protected static $tableName = 'neighborhoods';
    protected static $fillable = ['name', 'city', 'description', 'image'];

    public static function findByName($name) {
        $name = htmlspecialchars($name);
        $result = parent::where(['name' => $name]);
        return $result[0] ?? null;
    }

    public static function getDistinctCities() {
        $pdo = self::getPdo();
        $stmt = $pdo->query("SELECT DISTINCT city FROM neighborhoods ORDER BY city");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
