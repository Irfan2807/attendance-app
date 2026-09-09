<?php

namespace Tests\Unit;

use App\Models\Site;
use App\Services\AttendanceVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceVerificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_haversine_distance_computes_accurate_meter_distances(): void
    {
        // KL Tower to Petronas Twin Towers: approx ~1,100 to 1,200 meters
        $klTowerLat = 3.1528;
        $klTowerLon = 101.7038;

        $klccLat = 3.1578;
        $klccLon = 101.7120;

        $distance = AttendanceVerificationService::haversineDistance(
            $klTowerLat,
            $klTowerLon,
            $klccLat,
            $klccLon
        );

        $this->assertGreaterThan(1000, $distance);
        $this->assertLessThan(1300, $distance);

        // Same point distance should be 0
        $sameDistance = AttendanceVerificationService::haversineDistance(
            $klTowerLat,
            $klTowerLon,
            $klTowerLat,
            $klTowerLon
        );

        $this->assertEquals(0.0, $sameDistance);
    }

    public function test_verify_office_location_matches_within_radius_and_filters_far_sites(): void
    {
        $site = Site::create([
            'name' => 'HQ Office',
            'latitude' => 3.150000,
            'longitude' => 101.700000,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        // Very close coordinate (~10 meters away)
        $matched = AttendanceVerificationService::verifyOfficeLocation(3.150050, 101.700050);
        $this->assertNotNull($matched);
        $this->assertSame($site->id, $matched->id);

        // Far coordinate (over 2 km away)
        $farMatch = AttendanceVerificationService::verifyOfficeLocation(3.170000, 101.700000);
        $this->assertNull($farMatch);
    }
}

