<?php
namespace App\Database\Seeders;

use App\Config\DatabaseConnection;
use PDO;

class AdminSeeder extends BaseSeeder
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function run(): void
    {
        $email = 'admin@housing-kenya.com';
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existingUser = $stmt->fetch();

        if (!$existingUser) {
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([
                'Admin User',
                $email,
                $password,
                'admin'
            ]);
            echo "Admin user created successfully.\n";
        } else {
            echo "Admin user already exists.\n";
        }
    }
}
