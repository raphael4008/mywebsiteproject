<?php

namespace App\Database\Seeders;

use Faker\Factory;
use PDO;

class UserSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->truncate('users');
        $faker = Factory::create();

        $usersData = [
            // name, email, role, has_paid
            ['Admin User', 'admin@househunting.co.ke', 'admin', 1],
            ['John Landlord', 'landlord@househunting.co.ke', 'owner', 1],
            ['Jane Tenant', 'tenant@househunting.co.ke', 'user', 1],
            ['Alice Agent', 'agentuser@househunting.co.ke', 'agent', 1],
            ['Peter Jones', 'peter@example.com', 'user', 0],
            ['Landlord Two', 'landlord2@househunting.co.ke', 'owner', 1],
            ['Mary Anne', 'mary@example.com', 'user', 0],
            ['David Smith', 'd.smith@example.com', 'user', 1],
            ['New Owner', 'newowner@househunting.co.ke', 'owner', 0],
            ['Catherine User', 'catherine@example.com', 'user', 1],
            ['Robert Owner', 'robert@househunting.co.ke', 'owner', 1],
            ['Samantha Agent', 'samantha@househunting.co.ke', 'agent', 0],
            ['Michael Tenant', 'michael@example.com', 'user', 0],
            ['Property Admin', 'property_admin@househunting.co.ke', 'admin', 1],
            ['Owner Tenant', 'owner_tenant@househunting.co.ke', 'owner', 1],
            ['Sarah Property', 'sarah.p@househunting.co.ke', 'owner', 0],
            ['Admin Jane', 'admin.jane@househunting.co.ke', 'admin', 1],
        ];

        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role, has_paid) VALUES (?, ?, ?, ?, ?)");
        
        // Add existing users first
        foreach ($usersData as $userData) {
            $name = $userData[0];
            $email = $userData[1];
            $role = $userData[2]; 
            $hasPaid = $userData[3];
            $password = password_hash('password123', PASSWORD_DEFAULT); 
            
            $stmt->execute([$name, $email, $password, $role, $hasPaid]);
        }

        // Generate additional fake users
        for ($i = 0; $i < 20; $i++) {
            $name = $faker->name;
            $email = $faker->unique()->safeEmail;
            $password = password_hash('password123', PASSWORD_DEFAULT);
            $role = $faker->randomElement(['user', 'owner', 'agent']);
            $hasPaid = (int)$faker->boolean(70); // 70% chance of having paid

            $stmt->execute([$name, $email, $password, $role, $hasPaid]);
        }
        echo "Users seeded.\n";
    }
}
