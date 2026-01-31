<?php

namespace App\Database\Seeders;

use App\Models\Driver;
use PDO;

class DriverSeeder extends BaseSeeder
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function run(): void
    {
        $this->truncate('drivers');

        Driver::create([
            'name' => 'John Doe',
            'vehicle' => 'Pickup',
            'rating' => 4.5,
            'image' => null
        ]);

        Driver::create([
            'name' => 'Jane Smith',
            'vehicle' => 'Canter',
            'rating' => 4.8,
            'image' => null
        ]);

        Driver::create([
            'name' => 'Peter Jones',
            'vehicle' => 'Lorry',
            'rating' => 4.2,
            'image' => null
        ]);

        Driver::create([
            'name' => 'Samuel Green',
            'vehicle' => 'Pickup',
            'rating' => 4.6,
            'image' => null
        ]);

        Driver::create([
            'name' => 'Nancy Drew',
            'vehicle' => 'Canter',
            'rating' => 4.9,
            'image' => null
        ]);

        Driver::create([
            'name' => 'Bruce Wayne',
            'vehicle' => 'Lorry',
            'rating' => 4.1,
            'image' => null
        ]);

        Driver::create([
            'name' => 'Clark Kent',
            'vehicle' => 'Pickup',
            'rating' => 4.7,
            'image' => null
        ]);

        Driver::create([
            'name' => 'Diana Prince',
            'vehicle' => 'Canter',
            'rating' => 5.0,
            'image' => null
        ]);

        Driver::create([
            'name' => 'Barry Allen',
            'vehicle' => 'Lorry',
            'rating' => 4.0,
            'image' => null
        ]);

        Driver::create([
            'name' => 'Arthur Curry',
            'vehicle' => 'Pickup',
            'rating' => 4.4,
            'image' => null
        ]);
        echo "Drivers seeded.\n";
    }
}
