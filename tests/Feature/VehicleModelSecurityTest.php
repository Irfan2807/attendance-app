<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\MileageLog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleModelSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicle_mass_assignment_allows_fillable_and_protects_guarded(): void
    {
        $vehicle = Vehicle::create([
            'numberplate' => 'WXY1234',
            'name' => 'Field Truck',
            'current_mileage' => 15000,
            'next_service_mileage' => 20000,
            'is_active' => true,
            'notes' => 'Assigned to Team A',
        ]);

        $this->assertDatabaseHas('vehicles', [
            'numberplate' => 'WXY1234',
            'name' => 'Field Truck',
            'current_mileage' => 15000,
        ]);

        $this->assertSame(5000, $vehicle->kmUntilService());
        $this->assertFalse($vehicle->isServiceOverdue());
    }

    public function test_mileage_log_mass_assignment_allows_fillable(): void
    {
        $user = User::factory()->create(['role' => Role::Staff]);
        $vehicle = Vehicle::create([
            'numberplate' => 'BBA9999',
            'name' => 'Van 1',
            'current_mileage' => 5000,
            'next_service_mileage' => 10000,
            'is_active' => true,
        ]);

        $log = MileageLog::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'mileage_reading' => 5500,
            'recorded_at' => now(),
            'notes' => 'Trip to site',
        ]);

        $this->assertDatabaseHas('mileage_logs', [
            'id' => $log->id,
            'mileage_reading' => 5500,
        ]);
    }
}

