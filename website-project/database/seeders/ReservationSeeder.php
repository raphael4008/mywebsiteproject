<?php

namespace App\Database\Seeders;

use Faker\Factory;
use PDO;

class ReservationSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->truncate('reservations');
        $faker = Factory::create();

        // Fetch IDs for linking
        $userIds = $this->pdo->query("SELECT id FROM users WHERE role = 'user'")->fetchAll(PDO::FETCH_COLUMN); // Only users can make reservations
        $listingIds = $this->pdo->query("SELECT id FROM listings WHERE status IN ('available', 'reserved')")->fetchAll(PDO::FETCH_COLUMN);

        if (empty($userIds) || empty($listingIds)) {
            echo "Skipping ReservationSeeder: Not enough data in users (role 'user') or listings tables.\n";
            return;
        }

        $emailToUserIdMap = $this->pdo->query("SELECT email, id FROM users")->fetchAll(PDO::FETCH_KEY_PAIR);
        $listingTitleToListingIdMap = $this->pdo->query("SELECT title, id FROM listings")->fetchAll(PDO::FETCH_KEY_PAIR);

        $reservationsData = [
            [
                'listing_title' => 'Affordable & Secure Single Room with Own Bathroom',
                'user_email' => 'peter@example.com',
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'amount_paid' => 18000.00,
                'created_at' => '2024-03-01 10:00:00'
            ],
            [
                'listing_title' => 'Chic & Modern 1 Bedroom Furnished Apartment',
                'user_email' => 'michael@example.com',
                'status' => 'pending',
                'payment_status' => 'pending',
                'amount_paid' => 120000.00,
                'created_at' => '2024-03-08 14:00:00'
            ],
            [
                'listing_title' => 'Spacious 3 Bedroom Apartment with Lake View',
                'user_email' => 'mary@example.com',
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'amount_paid' => 95000.00,
                'created_at' => '2024-02-23 09:30:00'
            ],
        ];

        $stmt = $this->pdo->prepare("INSERT INTO reservations (listing_id, user_id, status, payment_status, amount_paid, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        
        // Seed static reservations
        foreach ($reservationsData as $reservation) {
            $listingId = $listingTitleToListingIdMap[$reservation['listing_title']] ?? null;
            $userId = $emailToUserIdMap[$reservation['user_email']] ?? null;

            if ($listingId && $userId) {
                $stmt->execute([
                    $listingId, $userId, $reservation['status'], $reservation['payment_status'], $reservation['amount_paid'], $reservation['created_at']
                ]);
            } else {
                echo "Skipping reservation for '{$reservation['listing_title']}': Listing or User not found.\n";
            }
        }

        // Generate additional fake reservations
        $statuses = ['pending', 'confirmed', 'cancelled'];
        $paymentStatuses = ['pending', 'paid'];

        for ($i = 0; $i < 20; $i++) {
            $listingId = $faker->randomElement($listingIds);
            $userId = $faker->randomElement($userIds);
            $status = $faker->randomElement($statuses);
            $paymentStatus = $faker->randomElement($paymentStatuses);
            $amountPaid = $faker->randomFloat(2, 5000, 500000);
            $createdAt = $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s');

            $stmt->execute([$listingId, $userId, $status, $paymentStatus, $amountPaid, $createdAt]);
        }
        echo "Reservations seeded.\n";
    }
}
