<?php

namespace Tests\Feature;

use App\Models\MileageLog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for UC-06 (Vehicle Registration) and UC-07 (Log Mileage).
 *
 * Covers vehicle service-status calculations and mileage log creation.
 */
class VehicleServiceStatusTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // UC-06: Vehicle service status calculations
    // -------------------------------------------------------------------------

    public function test_vehicle_service_is_ok_when_well_above_threshold(): void
    {
        $vehicle = Vehicle::create([
            'name'                 => 'Toyota Hilux',
            'numberplate'          => 'ABC1234',
            'current_mileage'      => 10000,
            'next_service_mileage' => 15000,
            'is_active'            => true,
        ]);

        $this->assertFalse($vehicle->isServiceOverdue());
        $this->assertFalse($vehicle->isServiceDueSoon());
        $this->assertSame(5000, $vehicle->kmUntilService());
    }

    public function test_vehicle_service_is_due_soon_within_500_km(): void
    {
        $vehicle = Vehicle::create([
            'name'                 => 'Toyota Hilux',
            'numberplate'          => 'ABC1234',
            'current_mileage'      => 14600,
            'next_service_mileage' => 15000,
            'is_active'            => true,
        ]);

        $this->assertFalse($vehicle->isServiceOverdue());
        $this->assertTrue($vehicle->isServiceDueSoon());
        $this->assertSame(400, $vehicle->kmUntilService());
    }

    public function test_vehicle_service_is_due_soon_at_exactly_500_km_remaining(): void
    {
        $vehicle = Vehicle::create([
            'name'                 => 'Toyota Hilux',
            'numberplate'          => 'ABC1234',
            'current_mileage'      => 14500,
            'next_service_mileage' => 15000,
            'is_active'            => true,
        ]);

        $this->assertTrue($vehicle->isServiceDueSoon());
        $this->assertSame(500, $vehicle->kmUntilService());
    }

    public function test_vehicle_service_is_overdue_when_current_mileage_exceeds_threshold(): void
    {
        $vehicle = Vehicle::create([
            'name'                 => 'Toyota Hilux',
            'numberplate'          => 'ABC1234',
            'current_mileage'      => 15500,
            'next_service_mileage' => 15000,
            'is_active'            => true,
        ]);

        $this->assertTrue($vehicle->isServiceOverdue());
        $this->assertSame(0, $vehicle->kmUntilService());
    }

    public function test_km_until_service_never_returns_negative(): void
    {
        $vehicle = Vehicle::create([
            'name'                 => 'Toyota Hilux',
            'numberplate'          => 'ABC1234',
            'current_mileage'      => 20000,
            'next_service_mileage' => 15000,
            'is_active'            => true,
        ]);

        $this->assertSame(0, $vehicle->kmUntilService());
    }

    // -------------------------------------------------------------------------
    // UC-07: Mileage log creation
    // -------------------------------------------------------------------------

    public function test_mileage_log_is_stored_with_correct_fields(): void
    {
        $user = User::factory()->create(['role' => 3]);
        $vehicle = Vehicle::create([
            'name'                 => 'Toyota Hilux',
            'numberplate'          => 'ABC1234',
            'current_mileage'      => 10000,
            'next_service_mileage' => 15000,
            'is_active'            => true,
        ]);

        $recordedAt = now();

        $log = MileageLog::create([
            'vehicle_id'      => $vehicle->id,
            'user_id'         => $user->id,
            'mileage_reading' => 10350,
            'notes'           => 'Site visit – KL HQ',
            'recorded_at'     => $recordedAt,
        ]);

        $this->assertDatabaseHas('mileage_logs', [
            'vehicle_id'      => $vehicle->id,
            'user_id'         => $user->id,
            'mileage_reading' => 10350,
        ]);
        $this->assertSame('Site visit – KL HQ', $log->notes);
    }

    public function test_vehicle_has_many_mileage_logs(): void
    {
        $user = User::factory()->create(['role' => 3]);
        $vehicle = Vehicle::create([
            'name'                 => 'Toyota Hilux',
            'numberplate'          => 'ABC1234',
            'current_mileage'      => 10000,
            'next_service_mileage' => 15000,
            'is_active'            => true,
        ]);

        MileageLog::create(['vehicle_id' => $vehicle->id, 'user_id' => $user->id, 'mileage_reading' => 10100, 'recorded_at' => now()]);
        MileageLog::create(['vehicle_id' => $vehicle->id, 'user_id' => $user->id, 'mileage_reading' => 10200, 'recorded_at' => now()]);
        MileageLog::create(['vehicle_id' => $vehicle->id, 'user_id' => $user->id, 'mileage_reading' => 10350, 'recorded_at' => now()]);

        $this->assertCount(3, $vehicle->mileageLogs);
    }
}
