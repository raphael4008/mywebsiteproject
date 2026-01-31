<?php

namespace App\Database\Seeders;

use Faker\Factory;
use PDO;

class ReviewSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->truncate('reviews');
        $faker = Factory::create();

        // Fetch IDs for linking
        $userIds = $this->pdo->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
        $listingIds = $this->pdo->query("SELECT id FROM listings")->fetchAll(PDO::FETCH_COLUMN);

        if (empty($userIds) || empty($listingIds)) {
            echo "Skipping ReviewSeeder: Not enough data in users or listings tables.\n";
            return;
        }
        
        $emailToUserIdMap = $this->pdo->query("SELECT email, id FROM users")->fetchAll(PDO::FETCH_KEY_PAIR);
        $listingTitleToListingIdMap = $this->pdo->query("SELECT title, id FROM listings")->fetchAll(PDO::FETCH_KEY_PAIR);


        $reviewsData = [
            [
                'listing_title' => 'Exquisite 5 Bedroom Ambassadorial Villa in Runda',
                'user_email' => 'tenant@househunting.co.ke',
                'rating' => 5,
                'comment' => 'Absolutely breathtaking property. The agent, Sarah, was professional and the photos dont do it justice. Very secure and peaceful.',
                'created_at' => '2023-09-01 10:00:00'
            ],
            [
                'listing_title' => 'Exquisite 5 Bedroom Ambassadorial Villa in Runda',
                'user_email' => 'peter@example.com',
                'rating' => 4,
                'comment' => 'Great place, but the garden needs a bit more work. Overall, a fantastic experience and well worth the price.',
                'created_at' => '2023-09-05 11:30:00'
            ],
            [
                'listing_title' => 'Serene 3 Bedroom Beachfront Cottage',
                'user_email' => 'tenant@househunting.co.ke',
                'rating' => 5,
                'comment' => 'The best deal in Mombasa! Waking up to the ocean breeze was a dream. Clean, safe, and very convenient. Highly recommend.',
                'created_at' => '2023-10-01 12:00:00'
            ],
            [
                'listing_title' => 'Charming 4 Bedroom Townhouse near National Park',
                'user_email' => 'mary@example.com',
                'rating' => 5,
                'comment' => 'Charming townhouse, felt very secure and close to nature. Loved the park access!',
                'created_at' => '2023-10-15 10:00:00'
            ],
            [
                'listing_title' => 'Luxury 2 Bedroom Apartment with Diani Beach Access',
                'user_email' => 'd.smith@example.com',
                'rating' => 4,
                'comment' => 'Diani apartment was beautiful, though a bit pricey. The ocean view made up for it!',
                'created_at' => '2023-11-20 14:30:00'
            ],
            [
                'listing_title' => 'Stylish 2 Bedroom Apartment in Vibrant Westlands',
                'user_email' => 'michael@example.com',
                'rating' => 3,
                'comment' => 'Westlands apartment was stylish but a bit noisy at night. Great location for work though.',
                'created_at' => '2024-01-05 09:00:00'
            ],
            [
                'listing_title' => 'Exquisite 5 Bedroom Ambassadorial Villa in Runda',
                'user_email' => 'mary@example.com',
                'rating' => 4,
                'comment' => 'Second stay here, still lovely. Agent is always responsive.',
                'created_at' => '2024-02-01 11:00:00'
            ],
        ];

        $stmt = $this->pdo->prepare("INSERT INTO reviews (listing_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, ?)");
        
        // Seed static reviews
        foreach ($reviewsData as $review) {
            $listingId = $listingTitleToListingIdMap[$review['listing_title']] ?? null;
            $userId = $emailToUserIdMap[$review['user_email']] ?? null;

            if ($listingId && $userId) {
                $stmt->execute([
                    $listingId, $userId, $review['rating'], $review['comment'], $review['created_at']
                ]);
            } else {
                echo "Skipping review for '{$review['listing_title']}': Listing or User not found.\n";
            }
        }

        // Generate additional fake reviews
        for ($i = 0; $i < 50; $i++) {
            $listingId = $faker->randomElement($listingIds);
            $userId = $faker->randomElement($userIds);
            $rating = $faker->numberBetween(1, 5);
            $comment = $faker->paragraph;
            $createdAt = $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s');

            $stmt->execute([$listingId, $userId, $rating, $comment, $createdAt]);
        }
        echo "Reviews seeded.\n";
    }
}
