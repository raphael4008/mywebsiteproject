<?php

namespace App\Database\Seeders;

use Faker\Factory;
use PDO;

class AmenitySeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->truncate('amenities');

        $amenitiesData = [
            ['WiFi', 'fa-wifi'], ['Parking', 'fa-parking'], ['Swimming Pool', 'fa-swimming-pool'],
            ['Air Conditioning', 'fa-wind'], ['Gym', 'fa-dumbbell'], ['24/7 Security', 'fa-shield-alt'],
            ['Balcony', 'fa-building'], ['Pet Friendly', 'fa-dog'], ['Backup Generator', 'fa-car-battery'],
            ['Borehole', 'fa-water'], ['High-Speed Lift', 'fa-elevator'], ['Rooftop Terrace', 'fa-umbrella-beach'],
            ['Garden', 'fa-leaf'], ['Dishwasher', 'fa-dishwasher'], ['Walk-in Closet', 'fa-person-booth'],
            ['Ocean View', 'fa-water'],
        ];

        $stmt = $this->pdo->prepare("INSERT INTO amenities (name, icon) VALUES (?, ?)");
        foreach ($amenitiesData as $amenity) {
            $stmt->execute($amenity);
        }
        echo "Amenities seeded.\n";
    }
}
