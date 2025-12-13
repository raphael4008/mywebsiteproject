<?php
namespace App\Models;

use Database;
use PDO;

class Message {
    public static function create($name, $email, $messageContent) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO messages (name, email, message) VALUES (:name, :email, :message)");

        // Sanitize and bind values
        $name = htmlspecialchars(strip_tags((string)$name));
        $email = htmlspecialchars(strip_tags((string)$email));
        $messageContent = htmlspecialchars(strip_tags((string)$messageContent));

        return $stmt->execute([
            'name' => $name,
            'email' => $email,
            'message' => $messageContent
        ]);
    }
}