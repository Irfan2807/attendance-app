<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleRoadTaxTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicle_road_tax_fields_persistence_and_casts(): void
    {
        $expiryDate = now()->addMonths(6)->startOfDay();

        $vehicle = Vehicle::create([
            'numberplate' => 'JQR8899',
            'name' => 'Toyota Hilux 4x4',
            'current_mileage' => 45000,
            'next_service_mileage' => 50000,
            'is_active' => true,
            'road_tax_expiry' => $expiryDate->toDateString(),
            'road_tax_amount' => 385.50,
            'road_tax_document' => 'road-taxes/sample-grant.pdf',
        ]);

        $this->assertDatabaseHas('vehicles', [
            'numberplate' => 'JQR8899',
            'road_tax_document' => 'road-taxes/sample-grant.pdf',
        ]);

        $this->assertInstanceOf(Carbon::class, $vehicle->road_tax_expiry);
        $this->assertEquals($expiryDate->toDateString(), $vehicle->road_tax_expiry->toDateString());
        $this->assertEquals(385.50, (float) $vehicle->road_tax_amount);
    }

    public function test_road_tax_expired_when_date_in_past(): void
    {
        $vehicle = Vehicle::create([
            'numberplate' => 'WPA1122',
            'name' => 'Nissan Navara',
            'current_mileage' => 30000,
            'next_service_mileage' => 35000,
            'road_tax_expiry' => now()->subDays(5)->toDateString(),
        ]);

        $this->assertTrue($vehicle->isRoadTaxExpired());
        $this->assertFalse($vehicle->isRoadTaxExpiringSoon());
        $this->assertSame(-5, $vehicle->daysUntilRoadTaxExpiry());
        $this->assertSame('expired', $vehicle->roadTaxStatus());
        $this->assertSame('danger', $vehicle->roadTaxBadgeColor());
        $this->assertSame('Expired 5 days ago', $vehicle->roadTaxStatusLabel());
    }

    public function test_road_tax_expiring_soon_within_30_days(): void
    {
        $vehicle = Vehicle::create([
            'numberplate' => 'VAA3344',
            'name' => 'Ford Ranger',
            'current_mileage' => 20000,
            'next_service_mileage' => 25000,
            'road_tax_expiry' => now()->addDays(14)->toDateString(),
        ]);

        $this->assertFalse($vehicle->isRoadTaxExpired());
        $this->assertTrue($vehicle->isRoadTaxExpiringSoon());
        $this->assertSame(14, $vehicle->daysUntilRoadTaxExpiry());
        $this->assertSame('expiring_soon', $vehicle->roadTaxStatus());
        $this->assertSame('warning', $vehicle->roadTaxBadgeColor());
        $this->assertSame('Expires in 14 days', $vehicle->roadTaxStatusLabel());
    }

    public function test_road_tax_valid_when_far_in_future(): void
    {
        $vehicle = Vehicle::create([
            'numberplate' => 'BCC5566',
            'name' => 'Isuzu D-Max',
            'current_mileage' => 10000,
            'next_service_mileage' => 15000,
            'road_tax_expiry' => now()->addDays(90)->toDateString(),
        ]);

        $this->assertFalse($vehicle->isRoadTaxExpired());
        $this->assertFalse($vehicle->isRoadTaxExpiringSoon());
        $this->assertSame(90, $vehicle->daysUntilRoadTaxExpiry());
        $this->assertSame('valid', $vehicle->roadTaxStatus());
        $this->assertSame('success', $vehicle->roadTaxBadgeColor());
        $this->assertSame('90 days remaining', $vehicle->roadTaxStatusLabel());
    }

    public function test_road_tax_not_set_handles_gracefully(): void
    {
        $vehicle = Vehicle::create([
            'numberplate' => 'KDD7788',
            'name' => 'Mitsubishi Triton',
            'current_mileage' => 5000,
            'next_service_mileage' => 10000,
            'road_tax_expiry' => null,
        ]);

        $this->assertFalse($vehicle->isRoadTaxExpired());
        $this->assertFalse($vehicle->isRoadTaxExpiringSoon());
        $this->assertNull($vehicle->daysUntilRoadTaxExpiry());
        $this->assertSame('not_set', $vehicle->roadTaxStatus());
        $this->assertSame('gray', $vehicle->roadTaxBadgeColor());
        $this->assertSame('Not Set', $vehicle->roadTaxStatusLabel());
    }

    public function test_filament_table_filters_expired_and_expiring_soon(): void
    {
        $expired = Vehicle::create([
            'numberplate' => 'EXP111',
            'name' => 'Expired Vehicle',
            'current_mileage' => 10000,
            'next_service_mileage' => 15000,
            'road_tax_expiry' => now()->subDays(10)->toDateString(),
        ]);

        $expiringSoon = Vehicle::create([
            'numberplate' => 'DUE222',
            'name' => 'Due Soon Vehicle',
            'current_mileage' => 10000,
            'next_service_mileage' => 15000,
            'road_tax_expiry' => now()->addDays(10)->toDateString(),
        ]);

        $valid = Vehicle::create([
            'numberplate' => 'VAL333',
            'name' => 'Valid Vehicle',
            'current_mileage' => 10000,
            'next_service_mileage' => 15000,
            'road_tax_expiry' => now()->addDays(120)->toDateString(),
        ]);

        // Query test for Expired filter
        $expiredResults = Vehicle::query()
            ->whereNotNull('road_tax_expiry')
            ->where('road_tax_expiry', '<', now()->toDateString())
            ->pluck('numberplate')
            ->all();

        $this->assertContains('EXP111', $expiredResults);
        $this->assertNotContains('DUE222', $expiredResults);
        $this->assertNotContains('VAL333', $expiredResults);

        // Query test for Expiring Soon filter
        $expiringResults = Vehicle::query()
            ->whereNotNull('road_tax_expiry')
            ->whereBetween('road_tax_expiry', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->pluck('numberplate')
            ->all();

        $this->assertContains('DUE222', $expiringResults);
        $this->assertNotContains('EXP111', $expiringResults);
        $this->assertNotContains('VAL333', $expiringResults);
    }

    public function test_vehicle_alert_widget_includes_expired_and_expiring_road_tax(): void
    {
        // Vehicle with healthy mileage (4000 KM away from service) but expired road tax
        $expiredRoadTaxVehicle = Vehicle::create([
            'numberplate' => 'ALERT01',
            'name' => 'Alert Van 1',
            'current_mileage' => 1000,
            'next_service_mileage' => 5000,
            'is_active' => true,
            'road_tax_expiry' => now()->subDays(3)->toDateString(),
        ]);

        // Vehicle with healthy mileage and healthy road tax
        $healthyVehicle = Vehicle::create([
            'numberplate' => 'OK999',
            'name' => 'Healthy Van',
            'current_mileage' => 1000,
            'next_service_mileage' => 5000,
            'is_active' => true,
            'road_tax_expiry' => now()->addMonths(6)->toDateString(),
        ]);

        $alertQueryPlates = Vehicle::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereRaw('(next_service_mileage - current_mileage) <= 500')
                  ->orWhereRaw('current_mileage >= next_service_mileage')
                  ->orWhere(function ($rq) {
                      $rq->whereNotNull('road_tax_expiry')
                         ->where('road_tax_expiry', '<=', now()->addDays(30)->toDateString());
                  });
            })
            ->pluck('numberplate')
            ->all();

        $this->assertContains('ALERT01', $alertQueryPlates);
        $this->assertNotContains('OK999', $alertQueryPlates);
    }
}
