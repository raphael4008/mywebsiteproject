<?php

namespace App\Database\Seeders;

use Faker\Factory;
use PDO;

class AgentSeeder extends BaseSeeder
{
    private array $allImagePaths = [
        'images/a.jpg', 'images/b.jpg', 'images/c.jpg', 'images/d.jpg', 'images/e.jpg',
        'images/f.jpeg', 'images/g.jpeg', 'images/h.jpeg', 'images/i.jpeg', 'images/j.jpeg',
        'images/k.jpeg', 'images/m.jpeg', 'images/n.jpeg', 'images/o.jpeg', 'images/p.jpeg',
        'images/q.jpeg', 'images/r.jpeg', 'images/s.jpeg', 'images/t.jpeg', 'images/u.jpeg',
        'images/v.jpeg', 'images/w.jpeg', 'images/x.jpeg', 'images/y.jpeg', 'images/z.jpeg'
    ];

    private function getRandomImages(int $count = 1): string
    {
        shuffle($this->allImagePaths);
        return $this->allImagePaths[array_rand($this->allImagePaths)];
    }

    public function run(): void
    {
        $this->truncate('agents');
        $faker = Factory::create();

        $agentsData = [
            ['Sarah Kamau', 'sarah@househunting.co.ke', '0700123456', 'Nairobi Specialist', 'Top rated agent in Westlands with over 10 years of experience.', 4.9, 25],
            ['John Omondi', 'john@househunting.co.ke', '0700654321', 'Mombasa Specialist', 'Expert in coastal properties, from apartments to beachfront villas.', 4.8, 18],
            ['Emily White', 'emily@househunting.co.ke', '0700987654', 'Kisumu Specialist', 'Finding you the best homes with lake views in Kisumu.', 4.9, 32],
            ['David Kimani', 'david@househunting.co.ke', '0712345678', 'Nakuru Properties', 'Your go-to agent for agricultural and residential properties in Nakuru county.', 4.7, 12],
            ['Grace Nabwire', 'grace@househunting.co.ke', '0723456789', 'Eldoret Homes', 'Specializing in family homes and new developments in Eldoret.', 4.8, 22],
            ['Daniel Ruto', 'daniel@househunting.co.ke', '0734567890', 'Urban Properties Expert', 'Focuses on modern apartments and commercial spaces in city centers.', 4.5, 15],
            ['Mercy Wanjiku', 'mercy@househunting.co.ke', '0745678901', 'Luxury Real Estate', 'Specializes in high-end properties and exclusive listings.', 5.0, 40],
            ['Collins Okoth', 'collins@househunting.co.ke', '0756789012', 'Rural & Land Sales', 'Extensive knowledge in agricultural land and plots for development.', 4.6, 10],
        ];

        $stmt = $this->pdo->prepare("INSERT INTO agents (name, email, phone, specialization, bio, image, rating, review_count) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Add existing agents first
        foreach ($agentsData as $agentData) {
            $image = $this->getRandomImages();
            $stmt->execute([
                $agentData[0], $agentData[1], $agentData[2], $agentData[3], $agentData[4],
                $image, $agentData[5], $agentData[6]
            ]);
        }

        // Generate additional fake agents
        for ($i = 0; $i < 5; $i++) {
            $name = $faker->name;
            $email = $faker->unique()->safeEmail;
            $phone = $faker->e164PhoneNumber;
            $specialization = $faker->jobTitle;
            $bio = $faker->paragraph;
            $image = $this->getRandomImages();
            $rating = $faker->randomFloat(2, 3.0, 5.0);
            $reviewCount = $faker->numberBetween(5, 100);

            $stmt->execute([$name, $email, $phone, $specialization, $bio, $image, $rating, $reviewCount]);
        }
        echo "Agents seeded.\n";
    }
}
