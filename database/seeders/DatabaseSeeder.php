<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test admin user
        User::factory()->create([
            'name' => 'Admin',
            'phone' => '0123456789',
            'password' => bcrypt('password'),
            'role' => 1,
        ]);

        // Create test manager user
        User::factory()->create([
            'name' => 'Manager',
            'phone' => '0198765432',
            'password' => bcrypt('password'),
            'role' => 2,
        ]);

        // Create test staff user
        User::factory()->create([
            'name' => 'Staff',
            'phone' => '0111111111',
            'password' => bcrypt('password'),
            'role' => 3,
        ]);
    }
}
