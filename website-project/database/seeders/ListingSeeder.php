<?php

namespace App\Database\Seeders;

use Faker\Factory;
use PDO;

class ListingSeeder extends BaseSeeder
{
    private array $allImagePaths = [
        'images/a.jpg', 'images/b.jpg', 'images/c.jpg', 'images/d.jpg', 'images/e.jpg',
        'images/f.jpeg', 'images/g.jpeg', 'images/h.jpeg', 'images/i.jpeg', 'images/j.jpeg',
        'images/k.jpeg', 'images/m.jpeg', 'images/n.jpeg', 'images/o.jpeg', 'images/p.jpeg',
        'images/q.jpeg', 'images/r.jpeg', 'images/s.jpeg', 'images/t.jpeg', 'images/u.jpeg',
        'images/v.jpeg', 'images/w.jpeg', 'images/x.jpeg', 'images/y.jpeg', 'images/z.jpeg'
    ];

    private function getRandomImages(int $count = 1): array
    {
        shuffle($this->allImagePaths);
        return array_slice($this->allImagePaths, 0, $count);
    }

    public function run(): void
    {
        $this->truncate('listing_amenities');
        $this->truncate('images');
        $this->truncate('listings');

        $faker = Factory::create();

        // Fetch IDs for linking
        $userIds = $this->pdo->query("SELECT id FROM users WHERE role = 'owner'")->fetchAll(PDO::FETCH_COLUMN);
        $agentIds = $this->pdo->query("SELECT id FROM users WHERE role = 'agent'")->fetchAll(PDO::FETCH_COLUMN);
        $neighborhoodIds = $this->pdo->query("SELECT id FROM neighborhoods")->fetchAll(PDO::FETCH_COLUMN);
        $amenities = $this->pdo->query("SELECT id, name FROM amenities")->fetchAll(PDO::FETCH_ASSOC);
        $amenityMap = array_column($amenities, 'id', 'name');
        $houseTypes = $this->pdo->query("SELECT id, name FROM house_types")->fetchAll(PDO::FETCH_ASSOC);
        $htypeNameToIdMap = [];
        foreach ($houseTypes as $htype) {
            $htypeNameToIdMap[strtolower(str_replace(' ', '_', $htype['name']))] = $htype['id'];
        }

        $styles = $this->pdo->query("SELECT id, name FROM styles")->fetchAll(PDO::FETCH_ASSOC);
        $styleNameToIdMap = [];
        foreach ($styles as $style) {
            $styleNameToIdMap[strtolower(str_replace(' ', '_', $style['name']))] = $style['id'];
        }

        if (empty($userIds) || empty($neighborhoodIds) || empty($amenityMap)) {
            echo "Skipping ListingSeeder: Not enough data in users (owners), neighborhoods, or amenities tables.\n";
            return;
        }

        $listingsData = [
            [
                'owner_id_email' => 'landlord@househunting.co.ke', 'agent_id_email' => 'agentuser@househunting.co.ke', 'neighborhood_name' => 'Runda',
                'title' => 'Exquisite 5 Bedroom Ambassadorial Villa in Runda',
                'description' => 'A magnificent family home set on a serene half-acre plot of manicured gardens. Features a large private pool, modern kitchen, and detached staff quarters for two. UN Blue Zone approved.',
                'htype' => 'house', 'rent_amount' => 450000, 'deposit_amount' => 900000, 'city' => 'Nairobi', 'latitude' => -1.2184, 'longitude' => 36.8272, 'status' => 'available', 'verified' => 1, 'furnished' => 0, 'is_featured' => 1,
                'amenities_names' => ['Parking', 'Swimming Pool', '24/7 Security', 'Backup Generator', 'Borehole', 'Rooftop Terrace', 'Garden']
            ],
            [
                'owner_id_email' => 'landlord2@househunting.co.ke', 'agent_id_email' => null, 'neighborhood_name' => 'Kilimani',
                'title' => 'Chic & Modern 1 Bedroom Furnished Apartment',
                'description' => 'A beautifully furnished apartment ideal for an expatriate or young couple. Located on a high floor with stunning city views. Walking distance to Yaya Centre.',
                'htype' => 'apartment', 'rent_amount' => 120000, 'deposit_amount' => 240000, 'city' => 'Nairobi', 'latitude' => -1.2898, 'longitude' => 36.7865, 'status' => 'rented', 'verified' => 1, 'furnished' => 1, 'is_featured' => 1,
                'amenities_names' => ['WiFi', 'Gym', 'Swimming Pool', '24/7 Security', 'High-Speed Lift', 'Rooftop Terrace', 'Air Conditioning']
            ],
            [
                'owner_id_email' => 'landlord@househunting.co.ke', 'agent_id_email' => 'samantha@househunting.co.ke', 'neighborhood_name' => 'Nyali',
                'title' => 'Serene 3 Bedroom Beachfront Cottage',
                'description' => 'Wake up to the sound of the waves. This charming cottage offers direct beach access and a private garden. Perfect for holidays or as a tranquil residence.',
                'htype' => 'house', 'rent_amount' => 180000, 'deposit_amount' => 180000, 'city' => 'Mombasa', 'latitude' => -4.0321, 'longitude' => 39.6961, 'status' => 'available', 'verified' => 1, 'furnished' => 1, 'is_featured' => 1,
                'amenities_names' => ['WiFi', 'Air Conditioning', 'Balcony', 'Parking', '24/7 Security', 'Pet Friendly', 'Ocean View']
            ],
            [
                'owner_id_email' => 'landlord2@househunting.co.ke', 'agent_id_email' => 'agentuser@househunting.co.ke', 'neighborhood_name' => 'Kileleshwa',
                'title' => 'Affordable & Secure Single Room with Own Bathroom',
                'description' => 'Clean and secure single room in a shared apartment complex, but with a private bathroom. Ideal for students or young professionals. Water and electricity included in rent.',
                'htype' => 'single_room', 'rent_amount' => 18000, 'deposit_amount' => 18000, 'city' => 'Nairobi', 'latitude' => -1.2721, 'longitude' => 36.7825, 'status' => 'reserved', 'verified' => 1, 'furnished' => 0, 'is_featured' => 1,
                'amenities_names' => ['24/7 Security', 'Borehole', 'WiFi']
            ],
            [
                'owner_id_email' => 'newowner@househunting.co.ke', 'agent_id_email' => null, 'neighborhood_name' => 'Karen',
                'title' => 'Classic 4 Bedroom Bungalow on One Acre',
                'description' => 'A charming older home with polished wood floors and a large fireplace. Set in a lush, mature garden with fruit trees. Very quiet and private.',
                'htype' => 'house', 'rent_amount' => 250000, 'deposit_amount' => 500000, 'city' => 'Nairobi', 'latitude' => -1.3192, 'longitude' => 36.7238, 'status' => 'available', 'verified' => 0, 'furnished' => 0, 'is_featured' => 1,
                'amenities_names' => ['Parking', 'Pet Friendly', 'Borehole', 'Garden']
            ],
            [
                'owner_id_email' => 'landlord@househunting.co.ke', 'agent_id_email' => 'agentuser@househunting.co.ke', 'neighborhood_name' => 'Milimani',
                'title' => 'Spacious 3 Bedroom Apartment with Lake View',
                'description' => 'Enjoy stunning views of Lake Victoria from this elegant 3 bedroom apartment. Features modern finishes, ample natural light, and access to a shared gym and pool.',
                'htype' => 'apartment', 'rent_amount' => 95000, 'deposit_amount' => 190000, 'city' => 'Kisumu', 'latitude' => -0.0917, 'longitude' => 34.7679, 'status' => 'available', 'verified' => 1, 'furnished' => 0, 'is_featured' => 1,
                'amenities_names' => ['Gym', 'Swimming Pool', '24/7 Security', 'High-Speed Lift', 'Balcony', 'WiFi']
            ],
            [
                'owner_id_email' => 'robert@househunting.co.ke', 'agent_id_email' => 'samantha@househunting.co.ke', 'neighborhood_name' => 'Langata',
                'title' => 'Charming 4 Bedroom Townhouse near National Park',
                'description' => 'A serene townhouse perfect for a family, located in a secure gated community. Proximity to Nairobi National Park offers a unique living experience. Includes a small private garden.',
                'htype' => 'townhouse', 'rent_amount' => 150000, 'deposit_amount' => 300000, 'city' => 'Nairobi', 'latitude' => -1.3333, 'longitude' => 36.7833, 'status' => 'available', 'verified' => 1, 'furnished' => 0, 'is_featured' => 0,
                'amenities_names' => ['Parking', 'Garden', 'Pet Friendly', '24/7 Security', 'Borehole']
            ],
            [
                'owner_id_email' => 'landlord2@househunting.co.ke', 'agent_id_email' => 'agentuser@househunting.co.ke', 'neighborhood_name' => 'Diani',
                'title' => 'Luxury 2 Bedroom Apartment with Diani Beach Access',
                'description' => 'Experience coastal living in this high-end apartment with direct access to Diani Beach. Fully furnished with modern amenities and spectacular ocean views.',
                'htype' => 'apartment', 'rent_amount' => 220000, 'deposit_amount' => 440000, 'city' => 'Mombasa', 'latitude' => -4.2709, 'longitude' => 39.5583, 'status' => 'available', 'verified' => 1, 'furnished' => 1, 'is_featured' => 0,
                'amenities_names' => ['WiFi', 'Swimming Pool', 'Air Conditioning', 'Ocean View', '24/7 Security', 'Balcony', 'Dishwasher']
            ],
            [
                'owner_id_email' => 'newowner@househunting.co.ke', 'agent_id_email' => null, 'neighborhood_name' => 'Muthaiga',
                'title' => 'Exclusive 6 Bedroom Mansion with sprawling gardens',
                'description' => 'A grand estate in the prestigious Muthaiga area. Features multiple living areas, a gourmet kitchen, staff quarters, and vast manicured gardens perfect for entertaining.',
                'htype' => 'house', 'rent_amount' => 800000, 'deposit_amount' => 1600000, 'city' => 'Nairobi', 'latitude' => -1.2500, 'longitude' => 36.8333, 'status' => 'available', 'verified' => 0, 'furnished' => 0, 'is_featured' => 0,
                'amenities_names' => ['Parking', 'Swimming Pool', 'Gym', '24/7 Security', 'Borehole', 'Garden', 'Dishwasher', 'Walk-in Closet']
            ],
            [
                'owner_id_email' => 'landlord@househunting.co.ke', 'agent_id_email' => 'samantha@househunting.co.ke', 'neighborhood_name' => 'Westlands',
                'title' => 'Stylish 2 Bedroom Apartment in Vibrant Westlands',
                'description' => 'Modern apartment in the heart of Westlands, offering convenience and luxury. Close to major business hubs, shopping centers, and entertainment spots.',
                'htype' => 'apartment', 'rent_amount' => 160000, 'deposit_amount' => 320000, 'city' => 'Nairobi', 'latitude' => -1.2657, 'longitude' => 36.8047, 'status' => 'available', 'verified' => 1, 'furnished' => 1, 'is_featured' => 0,
                'amenities_names' => ['WiFi', 'Gym', 'High-Speed Lift', '24/7 Security', 'Air Conditioning']
            ],
            [
                'owner_id_email' => 'landlord@househunting.co.ke', 'agent_id_email' => 'agentuser@househunting.co.ke', 'neighborhood_name' => 'Runda',
                'title' => 'Contemporary 4 Bedroom House with Smart Home Features',
                'description' => 'Newly built house with state-of-the-art smart home technology. Located in a quiet part of Runda, offering privacy and modern living.',
                'htype' => 'house', 'rent_amount' => 380000, 'deposit_amount' => 760000, 'city' => 'Nairobi', 'latitude' => -1.2100, 'longitude' => 36.8300, 'status' => 'available', 'verified' => 1, 'furnished' => 0, 'is_featured' => 0,
                'amenities_names' => ['Parking', 'Swimming Pool', '24/7 Security', 'Backup Generator', 'Garden', 'Dishwasher', 'WiFi']
            ],
            [
                'owner_id_email' => 'robert@househunting.co.ke', 'agent_id_email' => 'samantha@househunting.co.ke', 'neighborhood_name' => 'Nakuru Properties',
                'title' => 'Agricultural Land with Farmhouse in Nakuru',
                'description' => 'Expansive agricultural land suitable for various farming activities. Includes a rustic 3-bedroom farmhouse. Great investment opportunity.',
                'htype' => 'land', 'rent_amount' => 70000, 'deposit_amount' => 140000, 'city' => 'Nakuru', 'latitude' => -0.2833, 'longitude' => 36.0667, 'status' => 'available', 'verified' => 1, 'furnished' => 0, 'is_featured' => 0,
                'amenities_names' => ['Garden', 'Parking', 'Borehole']
            ],
            [
                'owner_id_email' => 'newowner@househunting.co.ke', 'agent_id_email' => null, 'neighborhood_name' => 'Karen',
                'title' => 'Cozy 2 Bedroom Cottage in Karen',
                'description' => 'A charming and private cottage ideal for a small family or couple. Nestled in a lush garden, offering peace and tranquility.',
                'htype' => 'house', 'rent_amount' => 110000, 'deposit_amount' => 220000, 'city' => 'Nairobi', 'latitude' => -1.3150, 'longitude' => 36.7200, 'status' => 'available', 'verified' => 0, 'furnished' => 1, 'is_featured' => 0,
                'amenities_names' => ['Parking', 'Pet Friendly', 'Garden', 'WiFi']
            ],
            [
                'owner_id_email' => 'landlord2@househunting.co.ke', 'agent_id_email' => 'agentuser@househunting.co.ke', 'neighborhood_name' => 'Kilimani',
                'title' => 'Modern Studio Apartment with City Views',
                'description' => 'Compact and efficient studio apartment with a great view of the city. Perfect for singles or as an investment property. Access to rooftop lounge.',
                'htype' => 'studio', 'rent_amount' => 60000, 'deposit_amount' => 120000, 'city' => 'Nairobi', 'latitude' => -1.2900, 'longitude' => 36.7850, 'status' => 'available', 'verified' => 1, 'furnished' => 1, 'is_featured' => 0,
                'amenities_names' => ['WiFi', 'Gym', 'High-Speed Lift', 'Rooftop Terrace']
            ],
            [
                'owner_id_email' => 'landlord@househunting.co.ke', 'agent_id_email' => 'samantha@househunting.co.ke', 'neighborhood_name' => 'Nyali',
                'title' => 'Vacation Rental: 5 Bedroom Villa with Private Pool',
                'description' => 'Stunning villa perfect for family vacations or group getaways. Features a private pool, chef on call, and walking distance to the beach.',
                'htype' => 'house', 'rent_amount' => 300000, 'deposit_amount' => 600000, 'city' => 'Mombasa', 'latitude' => -4.0300, 'longitude' => 39.7000, 'status' => 'available', 'verified' => 1, 'furnished' => 1, 'is_featured' => 0,
                'amenities_names' => ['Swimming Pool', 'Air Conditioning', 'Ocean View', '24/7 Security', 'WiFi', 'Dishwasher']
            ],
            [
                'owner_id_email' => 'robert@househunting.co.ke', 'agent_id_email' => null, 'neighborhood_name' => 'Eldoret Homes',
                'title' => 'New 3 Bedroom Home in Gated Community, Eldoret',
                'description' => 'Modern 3 bedroom home with spacious living areas and a manicured lawn. Located in a secure and family-friendly gated community.',
                'htype' => 'house', 'rent_amount' => 75000, 'deposit_amount' => 150000, 'city' => 'Eldoret', 'latitude' => 0.5143, 'longitude' => 35.2698, 'status' => 'available', 'verified' => 0, 'furnished' => 0, 'is_featured' => 0,
                'amenities_names' => ['Parking', 'Garden', 'Borehole', '24/7 Security']
            ],
            [
                'owner_id_email' => 'landlord2@househunting.co.ke', 'agent_id_email' => 'agentuser@househunting.co.ke', 'neighborhood_name' => 'Milimani',
                'title' => 'Executive Office Space in Kisumu CBD',
                'description' => 'Premium office space available for rent in a prime location in Kisumu CBD. Ideal for businesses looking for a professional environment.',
                'htype' => 'office', 'rent_amount' => 100000, 'deposit_amount' => 200000, 'city' => 'Kisumu', 'latitude' => -0.1000, 'longitude' => 34.7500, 'status' => 'available', 'verified' => 1, 'furnished' => 0, 'is_featured' => 0,
                'amenities_names' => ['WiFi', 'Parking', 'High-Speed Lift', '24/7 Security', 'Air Conditioning']
            ],
            [
                'owner_id_email' => 'newowner@househunting.co.ke', 'agent_id_email' => 'agentuser@househunting.co.ke', 'neighborhood_name' => 'Lavington',
                'title' => 'Luxurious 3 Bedroom Penthouse in Lavington',
                'description' => 'A stunning penthouse with panoramic city views. Features spacious interiors, a private terrace, and access to exclusive building amenities.',
                'htype' => 'apartment', 'rent_amount' => 280000, 'deposit_amount' => 560000, 'city' => 'Nairobi', 'latitude' => -1.2850, 'longitude' => 36.7800, 'status' => 'available', 'verified' => 1, 'furnished' => 1, 'is_featured' => 0,
                'amenities_names' => ['WiFi', 'Gym', 'Swimming Pool', '24/7 Security', 'High-Speed Lift', 'Rooftop Terrace', 'Dishwasher', 'Walk-in Closet']
            ],
            [
                'owner_id_email' => 'robert@househunting.co.ke', 'agent_id_email' => null, 'neighborhood_name' => 'Kileleshwa',
                'title' => 'Renovated 1 Bedroom Apartment, Kileleshwa',
                'description' => 'A freshly renovated one-bedroom apartment in a quiet part of Kileleshwa. Ideal for young professionals seeking comfort and convenience.',
                'htype' => 'apartment', 'rent_amount' => 70000, 'deposit_amount' => 140000, 'city' => 'Nairobi', 'latitude' => -1.2700, 'longitude' => 36.7800, 'status' => 'rented', 'verified' => 1, 'furnished' => 0, 'is_featured' => 0,
                'amenities_names' => ['WiFi', 'Parking', '24/7 Security']
            ],
            [
                'owner_id_email' => 'landlord@househunting.co.ke', 'agent_id_email' => 'agentuser@househunting.co.ke', 'neighborhood_name' => 'Muthaiga',
                'title' => 'Grand Colonial Style House in Muthaiga',
                'description' => 'A magnificent colonial-era house set on a large plot, retaining its original charm with modern upgrades. Perfect for a discerning family.',
                'htype' => 'house', 'rent_amount' => 600000, 'deposit_amount' => 1200000, 'city' => 'Nairobi', 'latitude' => -1.2550, 'longitude' => 36.8350, 'status' => 'available', 'verified' => 1, 'furnished' => 0, 'is_featured' => 0,
                'amenities_names' => ['Parking', 'Swimming Pool', 'Garden', '24/7 Security', 'Borehole', 'Dishwasher']
            ],
        ];

        // Fetch user (owner) IDs from database
        $ownerEmailToIdMap = $this->pdo->query("SELECT email, id FROM users WHERE role='owner'")->fetchAll(PDO::FETCH_KEY_PAIR);
        // Fetch agent IDs from database
        $agentEmailToIdMap = $this->pdo->query("SELECT email, id FROM users WHERE role = 'agent'")->fetchAll(PDO::FETCH_KEY_PAIR);
        // Fetch neighborhood IDs from database
        $neighborhoodNameToIdMap = $this->pdo->query("SELECT name, id FROM neighborhoods")->fetchAll(PDO::FETCH_KEY_PAIR);

        $stmt_listings = $this->pdo->prepare("INSERT INTO listings (owner_id, agent_id, neighborhood_id, title, description, htype_id, rent_amount, deposit_amount, city, latitude, longitude, status, verified, furnished, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_images = $this->pdo->prepare("INSERT INTO images (listing_id, path, is_main) VALUES (?, ?, ?)");
        $stmt_listing_amenities = $this->pdo->prepare("INSERT INTO listing_amenities (listing_id, amenity_id) VALUES (?, ?)");

        // Seed static listings
        foreach ($listingsData as $listing) {
            $ownerId = $ownerEmailToIdMap[$listing['owner_id_email']] ?? null;
            $agentId = $listing['agent_id_email'] ? ($agentEmailToIdMap[$listing['agent_id_email']] ?? null) : null;
            $neighborhoodId = $neighborhoodNameToIdMap[$listing['neighborhood_name']] ?? null;

            if ($ownerId === null || $neighborhoodId === null) {
                echo "Skipping listing '{$listing['title']}': Owner or Neighborhood not found.\n";
                continue;
            }
            
            $stmt_listings->execute([
                $ownerId,
                $agentId,
                $neighborhoodId,
                $listing['title'],
                $listing['description'],
                $htypeNameToIdMap[$listing['htype']] ?? null,
                $listing['rent_amount'],
                $listing['deposit_amount'],
                $listing['city'],
                $listing['latitude'],
                $listing['longitude'],
                $listing['status'],
                $listing['verified'],
                $listing['furnished'],
                $listing['is_featured'] ?? 0
            ]);
            $listingId = $this->pdo->lastInsertId();

            // Seed images for the listing
            $images = $this->getRandomImages(rand(1, 5));
            foreach ($images as $index => $imagePath) {
                $isMain = (int)($index === 0);
                $stmt_images->execute([$listingId, $imagePath, $isMain]);
            }

            // Seed amenities for the listing
            foreach ($listing['amenities_names'] as $amenityName) {
                if (isset($amenityMap[$amenityName])) {
                    $stmt_listing_amenities->execute([$listingId, $amenityMap[$amenityName]]);
                }
            }
        }

        // Generate additional fake listings
        $htypeIds = array_values($htypeNameToIdMap);
        $cities = ['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret'];
        $statuses = ['available', 'reserved', 'rented'];
        $allAmenityIds = array_column($amenities, 'id');

        for ($i = 0; $i < 30; $i++) {
            $ownerId = $faker->randomElement($userIds);
            $agentId = $faker->randomElement(array_merge($agentIds, [null])); // Some listings might not have an agent
            $neighborhoodId = $faker->randomElement($neighborhoodIds);

            $title = $faker->sentence(4);
            $description = $faker->paragraph(3);
            $htypeId = $faker->randomElement($htypeIds);
            $rentAmount = $faker->numberBetween(15000, 1000000);
            $depositAmount = $rentAmount * $faker->randomFloat(1, 1, 2);
            $city = $faker->randomElement($cities);
            $latitude = $faker->latitude;
            $longitude = $faker->longitude;
            $status = $faker->randomElement($statuses);
            $verified = (int)$faker->boolean(80);
            $furnished = (int)$faker->boolean(50);
            $isFeatured = (int)$faker->boolean(20);

            $stmt_listings->execute([
                $ownerId, $agentId, $neighborhoodId, $title, $description, $htypeId,
                $rentAmount, $depositAmount, $city, $latitude, $longitude, $status,
                $verified, $furnished, $isFeatured
            ]);
            $listingId = $this->pdo->lastInsertId();

            // Seed images for the fake listing
            $images = $this->getRandomImages(rand(1, 5));
            foreach ($images as $index => $imagePath) {
                $isMain = (int)($index === 0);
                $stmt_images->execute([$listingId, $imagePath, $isMain]);
            }

            // Seed random amenities for the fake listing
            $randomAmenityIds = $faker->randomElements($allAmenityIds, rand(1, count($allAmenityIds) / 2));
            foreach ($randomAmenityIds as $amenityId) {
                $stmt_listing_amenities->execute([$listingId, $amenityId]);
            }
        }
        echo "Listings, Images, and Amenities seeded.\n";
    }
}
