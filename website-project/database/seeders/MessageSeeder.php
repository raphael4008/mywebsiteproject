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

        $messagesData = [
            ['Mark Johnson', 'mark.j@email.com', 'Inquiry about Runda Villa', 'Hello, I am very interested in the 5-bedroom villa in Runda. Is it possible to schedule a viewing this weekend? Thank you.', '2024-03-12 10:00:00'],
            ['Linda Ole', 'linda.ole@email.com', 'Question on Pet Policy', 'I saw a lovely cottage in Nyali. I have two small dogs, is the property pet-friendly? Please let me know the policy.', '2024-03-10 14:30:00'],
            ['Client One', 'client1@example.com', 'Viewing Request for Diani Apartment', 'I would like to view the luxury 2-bedroom apartment in Diani next Tuesday. Is this possible?', '2024-03-13 09:00:00'],
            ['Potential Buyer', 'buyer@example.com', 'Information on Muthaiga Mansion', 'Could you provide more details on the 6-bedroom mansion in Muthaiga? Specifically, tax information.', '2024-03-14 11:00:00'],
        ];

        $stmt = $this->pdo->prepare("INSERT INTO messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, ?)");
        
        // Seed static messages
        foreach ($messagesData as $messageData) {
            $stmt->execute($messageData);
        }

        // Generate additional fake messages
        for ($i = 0; $i < 20; $i++) {
            $name = $faker->name;
            $email = $faker->email;
            $subject = $faker->sentence(5);
            $message = $faker->paragraph(3);
            $createdAt = $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s');

            $stmt->execute([$name, $email, $subject, $message, $createdAt]);
        }
        echo "Messages seeded.\n";
    }
}
