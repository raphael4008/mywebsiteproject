<?php

namespace App\Database\Seeders;

use Faker\Factory;
use PDO;

class SavedSearchSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->truncate('saved_searches');
        $faker = Factory::create();

        // Fetch IDs for linking
        $userIds = $this->pdo->query("SELECT id FROM users WHERE role = 'user' OR role = 'owner'")->fetchAll(PDO::FETCH_COLUMN); // Users or Owners can save searches

        if (empty($userIds)) {
            echo "Skipping SavedSearchSeeder: Not enough data in users table.\n";
            return;
        }
        
        $emailToUserIdMap = $this->pdo->query("SELECT email, id FROM users")->fetchAll(PDO::FETCH_KEY_PAIR);

        $savedSearchesData = [
            [
                'user_email' => 'peter@example.com',
                'criteria' => ['city' => 'Nairobi', 'htype' => 'apartment', 'max_rent' => 150000],
                'created_at' => '2024-02-15 08:00:00'
            ],
            [
                'user_email' => 'mary@example.com',
                'criteria' => ['city' => 'Mombasa', 'furnished' => true],
                'created_at' => '2024-02-22 13:00:00'
            ],
            [
                'user_email' => 'd.smith@example.com',
                'criteria' => ['city' => 'Kisumu', 'min_bedrooms' => 3],
                'created_at' => '2024-02-29 16:00:00'
            ],
            [
                'user_email' => 'michael@example.com',
                'criteria' => ['city' => 'Nairobi', 'neighborhood' => 'Karen', 'min_rent' => 100000],
                'created_at' => '2024-03-07 11:00:00'
            ],
        ];

        $stmt = $this->pdo->prepare("INSERT INTO saved_searches (user_id, criteria, created_at) VALUES (?, ?, ?)");
        
        // Seed static saved searches
        foreach ($savedSearchesData as $searchData) {
            $userId = $emailToUserIdMap[$searchData['user_email']] ?? null;
            
            if ($userId) {
                $stmt->execute([$userId, json_encode($searchData['criteria']), $searchData['created_at']]);
            } else {
                echo "Skipping saved search for user '{$searchData['user_email']}': User not found.\n";
            }
        }

        // Generate additional fake saved searches
        $cities = ['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret'];
        $htypes = ['apartment', 'house', 'studio', 'single_room', 'townhouse', 'land', 'office'];

        for ($i = 0; $i < 15; $i++) {
            $userId = $faker->randomElement($userIds);
            $criteria = [];
            
            if ($faker->boolean(70)) { // 70% chance to add city
                $criteria['city'] = $faker->randomElement($cities);
            }
            if ($faker->boolean(60)) { // 60% chance to add htype
                $criteria['htype'] = $faker->randomElement($htypes);
            }
            if ($faker->boolean(50)) { // 50% chance to add min_rent
                $criteria['min_rent'] = $faker->numberBetween(10000, 100000);
            }
            if ($faker->boolean(40)) { // 40% chance to add max_rent
                $criteria['max_rent'] = $faker->numberBetween(100000, 500000);
            }
            if ($faker->boolean(30)) { // 30% chance to add min_bedrooms
                $criteria['min_bedrooms'] = $faker->numberBetween(1, 5);
            }
            
            $createdAt = $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s');

            $stmt->execute([$userId, json_encode($criteria), $createdAt]);
        }
        echo "Saved Searches seeded.\n";
    }
}
