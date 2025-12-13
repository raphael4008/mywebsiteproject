
<?php
namespace App\Models;

use \PDO;

class OwnerPayment {
    public static function findByOwnerId($ownerId) {
        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM owner_payments WHERE owner_id = ? ORDER BY created_at DESC");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll();
    }

    public static function getAll() {
        $pdo = \Database::getInstance();
        $stmt = $pdo->query("SELECT * FROM owner_payments ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public static function updateStatus($id, $status) {
        $pdo = \Database::getInstance();
        $stmt = $pdo->prepare("UPDATE owner_payments SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}
