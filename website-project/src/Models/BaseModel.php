<?php
namespace App\Models;

use PDO;
use Exception;
use App\Config\DatabaseConnection;

abstract class BaseModel {
    protected static $tableName = '';
    protected static $primaryKey = 'id';
    protected static $fillable = []; // Fields that can be mass-assigned
    protected static $pdo;

    public function __construct() {
        if (empty(static::$tableName)) {
            $classParts = explode('\\', static::class);
            static::$tableName = strtolower(end($classParts)) . 's'; // Default table name is pluralized lowercase class name
        }
    }
    
    protected static function getPdo() {
        if (!self::$pdo) {
            self::$pdo = DatabaseConnection::getInstance()->getConnection();
        }
        return self::$pdo;
    }

    public static function find($id) {
        try {
            $pdo = self::getPdo();
            $stmt = $pdo->prepare("SELECT * FROM " . static::$tableName . " WHERE " . static::$primaryKey . " = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Database error in BaseModel::find(): " . $e->getMessage());
            return false;
        }
    }

    public static function all(): array {
        try {
            $pdo = self::getPdo();
            $stmt = $pdo->query("SELECT * FROM " . static::$tableName);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Database error in BaseModel::all(): " . $e->getMessage());
            return [];
        }
    }

    public static function create(array $data, ?PDO $pdo = null) {
        try {
            $pdo = $pdo ?? self::getPdo();
            $fillableData = static::filterFillable($data);

            if (empty($fillableData)) {
                throw new Exception("No fillable data provided for creation in " . static::class);
            }

            $columns = implode(', ', array_keys($fillableData));
            $placeholders = implode(', ', array_fill(0, count($fillableData), '?'));

            $sql = "INSERT INTO " . static::$tableName . " ($columns) VALUES ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($fillableData));

            return $pdo->lastInsertId();
        } catch (Exception $e) {
            error_log("Database error in BaseModel::create(): " . $e->getMessage());
            return false;
        }
    }

    public static function update($id, array $data, ?PDO $pdo = null): bool {
        try {
            $pdo = $pdo ?? self::getPdo();
            $fillableData = static::filterFillable($data);

            if (empty($fillableData)) {
                return true;
            }

            $setClauses = [];
            foreach ($fillableData as $key => $value) {
                $setClauses[] = "$key = ?";
            }
            $setClause = implode(', ', $setClauses);

            $sql = "UPDATE " . static::$tableName . " SET $setClause WHERE " . static::$primaryKey . " = ?";
            $stmt = $pdo->prepare($sql);

            $values = array_values($fillableData);
            $values[] = $id;

            return $stmt->execute($values);
        } catch (Exception $e) {
            error_log("Database error in BaseModel::update(): " . $e->getMessage());
            return false;
        }
    }

    public static function delete($id, ?PDO $pdo = null): bool {
        try {
            $pdo = $pdo ?? self::getPdo();
            $stmt = $pdo->prepare("DELETE FROM " . static::$tableName . " WHERE " . static::$primaryKey . " = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Database error in BaseModel::delete(): " . $e->getMessage());
            return false;
        }
    }

    public static function countAll(): int {
        try {
            $pdo = self::getPdo();
            $stmt = $pdo->query("SELECT COUNT(*) FROM " . static::$tableName);
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Database error in BaseModel::countAll(): " . $e->getMessage());
            return 0;
        }
    }

    protected static function filterFillable(array $data): array {
        if (empty(static::$fillable)) {
            return $data;
        }
        return array_intersect_key($data, array_flip(static::$fillable));
    }
    
    public static function where(array $conditions, string $orderBy = '', string $orderDirection = 'ASC', ?int $limit = null, ?int $offset = null): array {
        try {
            $pdo = self::getPdo();
            $sql = "SELECT * FROM " . static::$tableName;
            $params = [];
            
            if (!empty($conditions)) {
                $whereClauses = [];
                foreach($conditions as $key => $value) {
                    $whereClauses[] = "`$key` = ?";
                    $params[] = $value;
                }
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }

            if (!empty($orderBy)) {
                // Whitelist the orderBy column to prevent SQL injection
                if (in_array($orderBy, static::$fillable) || $orderBy === static::$primaryKey) {
                    $direction = (strtoupper($orderDirection) === 'DESC') ? 'DESC' : 'ASC';
                    $sql .= " ORDER BY `$orderBy` " . $direction;
                }
            }

            if ($limit !== null) {
                $sql .= " LIMIT :limit";
            }
            if ($offset !== null) {
                $sql .= " OFFSET :offset";
            }
            
            $stmt = $pdo->prepare($sql);

            // Bind LIMIT and OFFSET as integers
            if ($limit !== null) {
                $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            }
            if ($offset !== null) {
                $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            }
            
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Database error in BaseModel::where(): " . $e->getMessage());
            return [];
        }
    }

    public static function rawQuery(string $sql, array $params = [], bool $fetch = false) {
        try {
            $pdo = self::getPdo();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            if ($fetch) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Database error in BaseModel::rawQuery(): " . $e->getMessage());
            if ($fetch) return []; // Return empty array instead of false to prevent foreach errors
            return false;
        }
    }
}
