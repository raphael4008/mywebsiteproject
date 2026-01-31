<?php

namespace App\Database\Seeders;

use PDO;

class StyleSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->truncate('styles');

        $styles = [
            ['name' => 'MODERN'],
            ['name' => 'VINTAGE'],
            ['name' => 'TRADITIONAL'],
            ['name' => 'CONTEMPORARY'],
        ];

        $stmt = $this->pdo->prepare("INSERT INTO styles (name) VALUES (:name)");

        foreach ($styles as $style) {
            $stmt->execute($style);
        }
        echo "Styles seeded.\n";
    }
}

