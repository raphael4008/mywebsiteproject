<?php

namespace App\Database\Seeders;

use PDO;

class HouseTypeSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->truncate('house_types');

        $houseTypes = [
            'Apartment', 'House', 'Studio', 'Single Room', 'Townhouse', 'Land', 'Office'
        ];

        $stmt = $this->pdo->prepare("INSERT INTO house_types (name) VALUES (?)");

        foreach ($houseTypes as $type) {
            $stmt->execute([$type]);
        }
        
        echo "House Types seeded.\n";
    }
}