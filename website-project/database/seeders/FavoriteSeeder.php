<?php

namespace App\Database\Seeders;

use Faker\Factory;
use PDO;

class FavoriteSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->truncate('favorites');
        $faker = Factory::create();

        // Fetch IDs for linking
        $userIds = $this->pdo->query("SELECT id FROM users WHERE role = 'user' OR role = 'owner'")->fetchAll(PDO::FETCH_COLUMN); // Users or Owners can have favorites
        $listingIds = $this->pdo->query("SELECT id FROM listings")->fetchAll(PDO::FETCH_COLUMN);

        if (empty($userIds) || empty($listingIds)) {
            echo "Skipping FavoriteSeeder: Not enough data in users or listings tables.\n";
            return;
        }

        $emailToUserIdMap = $this->pdo->query("SELECT email, id FROM users")->fetchAll(PDO::FETCH_KEY_PAIR);
        $listingTitleToListingIdMap = $this->pdo->query("SELECT title, id FROM listings")->fetchAll(PDO::FETCH_KEY_PAIR);

        $favoritesData = [
            [
                'user_email' => 'tenant@househunting.co.ke',
                'listing_title' => 'Exquisite 5 Bedroom Ambassadorial Villa in Runda',
            ],
            [
                'user_email' => 'tenant@househunting.co.ke',
                'listing_title' => 'Serene 3 Bedroom Beachfront Cottage',
            ],
            [
                'user_email' => 'peter@example.com',
                'listing_title' => 'Exquisite 5 Bedroom Ambassadorial Villa in Runda',
            ],
            [
                'user_email' => 'peter@example.com',
                'listing_title' => 'Affordable & Secure Single Room with Own Bathroom',
            ],
            [
                'user_email' => 'mary@example.com',
                'listing_title' => 'Charming 4 Bedroom Townhouse near National Park',
            ],
            [
                'user_email' => 'd.smith@example.com',
                'listing_title' => 'Luxury 2 Bedroom Apartment with Diani Beach Access',
            ],
            [
                'user_email' => 'michael@example.com',
                'listing_title' => 'Stylish 2 Bedroom Apartment in Vibrant Westlands',
            ],
            [
                'user_email' => 'tenant@househunting.co.ke',
                'listing_title' => 'Spacious 3 Bedroom Apartment with Lake View',
            ],
        ];

        $stmt = $this->pdo->prepare("INSERT INTO favorites (user_id, listing_id, created_at) VALUES (?, ?, ?)");
        
        // Seed static favorites
        foreach ($favoritesData as $favorite) {
            $userId = $emailToUserIdMap[$favorite['user_email']] ?? null;
            $listingId = $listingTitleToListingIdMap[$favorite['listing_title']] ?? null;
            $createdAt = $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'); // Random date for existing favorites
            
            if ($userId && $listingId) {
                // Check for uniqueness before inserting
                $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND listing_id = ?");
                $checkStmt->execute([$userId, $listingId]);
                if ($checkStmt->fetchColumn() == 0) {
                    $stmt->execute([$userId, $listingId, $createdAt]);
                }
            } else {
                echo "Skipping favorite for user '{$favorite['user_email']}' and listing '{$favorite['listing_title']}': User or Listing not found.\n";
            }
        }


        // Generate additional fake favorites
        for ($i = 0; $i < 30; $i++) {
            $userId = $faker->randomElement($userIds);
            $listingId = $faker->randomElement($listingIds);
            $createdAt = $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s');

            // Ensure unique user_id, listing_id combination
            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND listing_id = ?");
            $checkStmt->execute([$userId, $listingId]);
            if ($checkStmt->fetchColumn() == 0) {
                $stmt->execute([$userId, $listingId, $createdAt]);
            }
        }
        echo "Favorites seeded.\n";
    }
}
