<?php
namespace App\Models;

class Agent {
    public static function getAll() {
        $pdo = \Database::getInstance();
        $stmt = $pdo->query("SELECT * FROM agents");
        return $stmt->fetchAll();
    }

    public static function findById($id) {
        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM agents WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
