<?php

namespace App\Database\Seeders;

use Faker\Factory;
use PDO;

class NeighborhoodSeeder extends BaseSeeder
{
    private array $allImagePaths = [
        'images/1.jpeg', 'images/a.jpg', 'images/b.jpg', 'images/c.jpg', 'images/d.jpg', 'images/e.jpg',
        'images/f.jpeg', 'images/g.jpeg', 'images/h.jpeg', 'images/i.jpeg', 'images/j.jpeg',
        'images/k.jpeg', 'images/m.jpeg', 'images/n.jpeg', 'images/o.jpeg', 'images/p.jpeg',
        'images/q.jpeg', 'images/r.jpeg', 'images/s.jpeg', 'images/t.jpeg', 'images/u.jpeg',
        'images/v.jpeg', 'images/w.jpeg', 'images/x.jpeg', 'images/y.jpeg', 'images/z.jpeg'
    ];

    private function getRandomImage(): string
    {
        return $this->allImagePaths[array_rand($this->allImagePaths)];
    }

    public function run(): void
    {
        $this->truncate('neighborhoods');
        $faker = Factory::create();

        $neighborhoodsData = [
            ['Westlands', 'Nairobi', 'An affluent, high-energy neighborhood known for its corporate offices, shopping malls, and vibrant nightlife.'],
            ['Kilimani', 'Nairobi', 'A bustling, cosmopolitan area with a mix of older apartments and modern high-rises, popular with young professionals.'],
            ['Nyali', 'Mombasa', 'An upscale coastal residential area known for its luxury homes, beach resorts, and the Nyali Golf Club.'],
            ['Milimani', 'Kisumu', 'A quiet and leafy suburb overlooking Lake Victoria, home to luxury hotels and government residences.'],
            ['Runda', 'Nairobi', 'One of Nairobis most exclusive suburbs, known for its large, leafy properties, embassies, and serene environment.'],
            ['Kileleshwa', 'Nairobi', 'A quiet, upper-middle-class residential area characterized by a mix of old bungalows and new apartment complexes.'],
            ['Karen', 'Nairobi', 'A green and spacious suburb with a rich colonial history, famous for the Karen Blixen Museum and large family homes.'],
            ['Lavington', 'Nairobi', 'An affluent suburb with a mix of residential and commercial properties, known for its good schools and quiet streets.'],
            ['Diani', 'Mombasa', 'Famous for its pristine white sand beaches and clear turquoise waters, a top tourist destination with luxury resorts and villas.'],
            ['Muthaiga', 'Nairobi', 'An exclusive, leafy residential suburb with large mansions, renowned golf club, and high-security embassies.'],
            ['Langata', 'Nairobi', 'Home to the Nairobi National Park, Giraffe Centre, and Bomas of Kenya, offering a blend of wildlife and cultural experiences.'],
            ['Nakuru Properties', 'Nakuru', 'A fast-growing town known for Lake Nakuru National Park and agricultural surroundings.'],
            ['Eldoret Homes', 'Eldoret', 'A major city in western Kenya, known as a hub for agriculture and athletics.'],
        ];

        $stmt = $this->pdo->prepare("INSERT INTO neighborhoods (name, city, description, image) VALUES (?, ?, ?, ?)");
        
        // Add existing neighborhoods first
        foreach ($neighborhoodsData as $neighborhoodData) {
            $image = $this->getRandomImage();
            $stmt->execute([
                $neighborhoodData[0], $neighborhoodData[1], $neighborhoodData[2], $image
            ]);
        }

        // Generate additional fake neighborhoods
        for ($i = 0; $i < 10; $i++) {
            $name = $faker->city;
            $city = $faker->city;
            $description = $faker->paragraph;
            $image = $this->getRandomImage();

            $stmt->execute([$name, $city, $description, $image]);
        }
        echo "Neighborhoods seeded.\n";
    }
}
