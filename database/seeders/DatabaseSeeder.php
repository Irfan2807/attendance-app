<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\User;
use App\Models\Vehicle;
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
        // 1. Users
        $admin = User::firstOrCreate(
            ['phone' => '0123456789'],
            [
                'name' => 'Director Admin',
                'password' => bcrypt('password'),
                'role' => 1,
                'is_active' => true,
            ]
        );

        $manager = User::firstOrCreate(
            ['phone' => '0198765432'],
            [
                'name' => 'Site Manager',
                'password' => bcrypt('password'),
                'role' => 2,
                'is_active' => true,
            ]
        );

        $hr = User::firstOrCreate(
            ['phone' => '0144444444'],
            [
                'name' => 'HR Executive',
                'password' => bcrypt('password'),
                'role' => 4,
                'is_active' => true,
            ]
        );

        $staff = User::firstOrCreate(
            ['phone' => '0111111111'],
            [
                'name' => 'Field Staff',
                'password' => bcrypt('password'),
                'role' => 3,
                'manager_id' => $manager->id,
                'is_active' => true,
            ]
        );

        // Ensure staff is linked to manager
        if (! $staff->manager_id) {
            $staff->update(['manager_id' => $manager->id]);
        }

        // 2. Demo Sites
        Site::firstOrCreate(
            ['name' => 'HQ Office - Subang Jaya'],
            [
                'latitude' => 3.0559,
                'longitude' => 101.5636,
                'ip_address' => '127.0.0.1', // Localhost IP for instant demo verification
                'radius_meters' => 200,
                'is_active' => true,
            ]
        );

        Site::firstOrCreate(
            ['name' => 'Tower Alpha - Bukit Jelutong'],
            [
                'latitude' => 3.1008,
                'longitude' => 101.5312,
                'ip_address' => null,
                'radius_meters' => 150,
                'is_active' => true,
            ]
        );

        Site::firstOrCreate(
            ['name' => 'Depot & Workshop - Kota Damansara'],
            [
                'latitude' => 3.1517,
                'longitude' => 101.5878,
                'ip_address' => null,
                'radius_meters' => 250,
                'is_active' => true,
            ]
        );

        // 3. Demo Fleet Vehicles
        Vehicle::firstOrCreate(
            ['numberplate' => 'WXX 4821'],
            [
                'name' => 'Toyota Hilux 2.8 (Rigging Unit 1)',
                'current_mileage' => 49650,
                'next_service_mileage' => 50000, // 350 km remaining -> Service Due Soon (Amber Alert)
                'is_active' => true,
                'notes' => 'Equipped with tower fall-arrest rigging kits and winch.',
            ]
        );

        Vehicle::firstOrCreate(
            ['numberplate' => 'VAB 8293'],
            [
                'name' => 'Ford Ranger 2.0 (Field Service 2)',
                'current_mileage' => 32100,
                'next_service_mileage' => 40000, // OK / Good Standing
                'is_active' => true,
                'notes' => 'Civil & CME maintenance truck with portable generator.',
            ]
        );

        Vehicle::firstOrCreate(
            ['numberplate' => 'BMQ 5104'],
            [
                'name' => 'Isuzu D-Max 3.0 (Fiber Rollout)',
                'current_mileage' => 61250,
                'next_service_mileage' => 60000, // Overdue (Red Alert)
                'is_active' => true,
                'notes' => 'Fiber splicing & OTDR testing equipment carriage.',
            ]
        );
    }
}
