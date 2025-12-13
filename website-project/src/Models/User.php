<?php
namespace App\Models;

use \PDO;

class User {
    public static function findById($id) {
        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function update($id, $data) {
        $pdo = \Database::getInstance();
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
        }
        $params = array_values($data);
        $params[] = $id;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }
}
