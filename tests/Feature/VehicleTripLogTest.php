<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Staff\Resources\MileageLogResource\Pages\CreateMileageLog;
use App\Models\MileageLog;
use App\Models\Site;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VehicleTripLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_log_computes_distance_and_syncs_mileage_reading(): void
    {
        $user = User::factory()->create(['role' => Role::Staff]);
        $vehicle = Vehicle::create([
            'numberplate' => 'WXX4821',
            'name' => 'Toyota Hilux',
            'current_mileage' => 49000,
            'next_service_mileage' => 50000,
            'is_active' => true,
        ]);

        $log = MileageLog::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'start_mileage' => 49000,
            'end_mileage' => 49085,
            'destination' => 'Tower Alpha - Bukit Jelutong',
            'purpose' => 'CME & Telecom Rigging',
            'recorded_at' => now(),
        ]);

        $this->assertEquals(85, $log->distance_km);
        $this->assertEquals(49085, $log->mileage_reading);
        $this->assertEquals('Tower Alpha - Bukit Jelutong', $log->resolved_destination);
    }

    public function test_trip_log_records_destination_purpose_and_site_relation(): void
    {
        $user = User::factory()->create(['role' => Role::Staff]);
        $vehicle = Vehicle::create([
            'numberplate' => 'VAB8293',
            'name' => 'Ford Ranger',
            'current_mileage' => 32000,
            'next_service_mileage' => 40000,
            'is_active' => true,
        ]);

        $site = Site::create([
            'name' => 'Depot & Workshop - Kota Damansara',
            'latitude' => 3.1517,
            'longitude' => 101.5878,
            'radius_meters' => 250,
            'is_active' => true,
        ]);

        $log = MileageLog::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'start_mileage' => 32000,
            'end_mileage' => 32060,
            'site_id' => $site->id,
            'destination' => $site->name,
            'purpose' => 'Material & Tool Transport',
            'recorded_at' => now(),
        ]);

        $this->assertEquals($site->id, $log->site_id);
        $this->assertEquals(60, $log->distance_km);
        $this->assertEquals('Depot & Workshop - Kota Damansara', $log->resolved_destination);
    }

    public function test_resolved_destination_helper_uses_site_or_custom_text(): void
    {
        $user = User::factory()->create(['role' => Role::Staff]);
        $vehicle = Vehicle::create([
            'numberplate' => 'BMQ5104',
            'name' => 'Isuzu D-Max',
            'current_mileage' => 60000,
            'next_service_mileage' => 65000,
            'is_active' => true,
        ]);

        $site = Site::create([
            'name' => 'HQ Office - Subang Jaya',
            'latitude' => 3.0559,
            'longitude' => 101.5636,
            'radius_meters' => 200,
            'is_active' => true,
        ]);

        $logWithSite = MileageLog::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'start_mileage' => 60000,
            'end_mileage' => 60020,
            'site_id' => $site->id,
            'purpose' => 'Routine Vehicle Servicing',
            'recorded_at' => now(),
        ]);

        $this->assertEquals('HQ Office - Subang Jaya', $logWithSite->resolved_destination);

        $logWithCustomDest = MileageLog::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'start_mileage' => 60020,
            'end_mileage' => 60080,
            'destination' => 'Petronas Gas Terminal, Kerteh',
            'purpose' => 'Site Survey & Inspection',
            'recorded_at' => now(),
        ]);

        $this->assertEquals('Petronas Gas Terminal, Kerteh', $logWithCustomDest->resolved_destination);
    }

    public function test_staff_can_log_trip_through_filament_page(): void
    {
        $staff = User::factory()->create(['role' => Role::Staff]);
        $vehicle = Vehicle::create([
            'numberplate' => 'WXX4821',
            'name' => 'Toyota Hilux 2.8',
            'current_mileage' => 49500,
            'next_service_mileage' => 50000,
            'is_active' => true,
        ]);

        $this->actingAs($staff);
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('staff'));

        Livewire::test(CreateMileageLog::class)
            ->fillForm([
                'vehicle_id' => $vehicle->id,
                'destination_type' => 'custom_location',
                'destination' => 'Tower Bravo - Rawang',
                'purpose' => 'Emergency Maintenance',
                'start_mileage' => 49500,
                'end_mileage' => 49620,
                'recorded_at' => now()->toDateTimeString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('mileage_logs', [
            'vehicle_id' => $vehicle->id,
            'user_id' => $staff->id,
            'start_mileage' => 49500,
            'end_mileage' => 49620,
            'distance_km' => 120,
            'destination' => 'Tower Bravo - Rawang',
            'purpose' => 'Emergency Maintenance',
            'mileage_reading' => 49620,
        ]);

        // Verify vehicle current mileage was updated to ending mileage
        $vehicle->refresh();
        $this->assertEquals(49620, $vehicle->current_mileage);
        // Next service 50000 - 49620 = 380 km left -> Service Due Soon!
        $this->assertTrue($vehicle->isServiceDueSoon());
    }
}
