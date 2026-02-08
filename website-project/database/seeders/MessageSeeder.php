<?php

namespace App\Database\Seeders;

use Faker\Factory;
use PDO;

class MessageSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->truncate('messages');
        $faker = Factory::create();

        $users = $this->pdo->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
        $listings = $this->pdo->query("SELECT id FROM listings")->fetchAll(PDO::FETCH_COLUMN);

        if (count($users) < 2 || count($listings) === 0) {
            echo "Skipping MessageSeeder: Not enough data in users or listings tables.\n";
            return;
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO messages (listing_id, sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, ?, ?)"
        );

        for ($i = 0; $i < 20; $i++) {
            $listingId = $listings[array_rand($listings)];
            $senderId = $users[array_rand($users)];
            $receiverId = $users[array_rand($users)];
            // Make sure sender and receiver are not the same
            while ($senderId === $receiverId) {
                $receiverId = $users[array_rand($users)];
            }

            $message = $faker->paragraph(3);
            $createdAt = $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s');

            $stmt->execute([$listingId, $senderId, $receiverId, $message, $createdAt]);
        }
        echo "Messages seeded.\n";
    }
}
