<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Site;
use App\Models\User;
use App\Services\AttendanceVerificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceVerificationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    // ----------------------------------------------------------------
    // verifyOfficeIp
    // ----------------------------------------------------------------

    public function test_verify_office_ip_matches_active_site(): void
    {
        Site::create([
            'name'         => 'HQ',
            'latitude'     => 3.1390,
            'longitude'    => 101.6869,
            'radius_meters'=> 100,
            'is_active'    => true,
            'ip_address'   => '203.0.113.10',
        ]);

        $site = AttendanceVerificationService::verifyOfficeIp('203.0.113.10');

        $this->assertNotNull($site);
        $this->assertSame('HQ', $site->name);
    }

    public function test_verify_office_ip_returns_null_for_unknown_ip(): void
    {
        Site::create([
            'name'         => 'HQ',
            'latitude'     => 3.1390,
            'longitude'    => 101.6869,
            'radius_meters'=> 100,
            'is_active'    => true,
            'ip_address'   => '203.0.113.10',
        ]);

        $site = AttendanceVerificationService::verifyOfficeIp('1.2.3.4');

        $this->assertNull($site);
    }

    public function test_verify_office_ip_returns_null_for_inactive_site(): void
    {
        Site::create([
            'name'         => 'Branch',
            'latitude'     => 3.1390,
            'longitude'    => 101.6869,
            'radius_meters'=> 100,
            'is_active'    => false,
            'ip_address'   => '203.0.113.20',
        ]);

        $site = AttendanceVerificationService::verifyOfficeIp('203.0.113.20');

        $this->assertNull($site);
    }

    public function test_verify_office_ip_returns_null_when_no_ip_given(): void
    {
        $site = AttendanceVerificationService::verifyOfficeIp(null);

        $this->assertNull($site);
    }

    // ----------------------------------------------------------------
    // verifyOfficeLocation (Haversine / GPS)
    // ----------------------------------------------------------------

    public function test_verify_office_location_matches_when_within_radius(): void
    {
        // Kuala Lumpur City Centre (~0 m from the site coords)
        Site::create([
            'name'         => 'KL Office',
            'latitude'     => 3.1579,
            'longitude'    => 101.7123,
            'radius_meters'=> 100,
            'is_active'    => true,
        ]);

        // Exact same coordinates – distance = 0
        $site = AttendanceVerificationService::verifyOfficeLocation(3.1579, 101.7123);

        $this->assertNotNull($site);
        $this->assertSame('KL Office', $site->name);
    }

    public function test_verify_office_location_returns_null_when_outside_radius(): void
    {
        Site::create([
            'name'         => 'KL Office',
            'latitude'     => 3.1579,
            'longitude'    => 101.7123,
            'radius_meters'=> 100,
            'is_active'    => true,
        ]);

        // ~1 km away (Petronas Towers area)
        $site = AttendanceVerificationService::verifyOfficeLocation(3.1578, 101.7223);

        $this->assertNull($site);
    }

    public function test_verify_office_location_respects_custom_radius(): void
    {
        Site::create([
            'name'         => 'Wide Site',
            'latitude'     => 3.1579,
            'longitude'    => 101.7123,
            'radius_meters'=> 50,
            'is_active'    => true,
        ]);

        // ~30 m away – within 50 m radius
        $siteClose = AttendanceVerificationService::verifyOfficeLocation(
            3.1579,
            101.71233, // ~30 m east
            50
        );

        // ~200 m away – outside 50 m radius
        $siteFar = AttendanceVerificationService::verifyOfficeLocation(
            3.1579,
            101.7141, // ~200 m east
            50
        );

        $this->assertNotNull($siteClose);
        $this->assertNull($siteFar);
    }

    public function test_verify_office_location_returns_null_when_no_active_sites_exist(): void
    {
        $site = AttendanceVerificationService::verifyOfficeLocation(3.1579, 101.7123);

        $this->assertNull($site);
    }

    public function test_verify_office_location_ignores_inactive_sites(): void
    {
        Site::create([
            'name'         => 'Closed Site',
            'latitude'     => 3.1579,
            'longitude'    => 101.7123,
            'radius_meters'=> 1000,
            'is_active'    => false,
        ]);

        $site = AttendanceVerificationService::verifyOfficeLocation(3.1579, 101.7123);

        $this->assertNull($site);
    }

    // ----------------------------------------------------------------
    // verifyGroupClockIn
    // ----------------------------------------------------------------

    public function test_group_clock_in_returns_true_when_enough_staff_nearby(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 3]);

        // Create 5 recent clock-ins within 50 m of the target location
        for ($i = 0; $i < 5; $i++) {
            Attendance::create([
                'user_id'       => $user->id,
                'latitude'      => 3.1579,
                'longitude'     => 101.7123,
                'status'        => 'pending',
                'clock_in_time' => now()->subMinutes(30),
            ]);
        }

        $result = AttendanceVerificationService::verifyGroupClockIn(3.1579, 101.7123);

        $this->assertTrue($result);
    }

    public function test_group_clock_in_returns_false_when_not_enough_staff_nearby(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 3]);

        // Only 2 clock-ins (below the default threshold of 5)
        for ($i = 0; $i < 2; $i++) {
            Attendance::create([
                'user_id'       => $user->id,
                'latitude'      => 3.1579,
                'longitude'     => 101.7123,
                'status'        => 'pending',
                'clock_in_time' => now()->subMinutes(30),
            ]);
        }

        $result = AttendanceVerificationService::verifyGroupClockIn(3.1579, 101.7123);

        $this->assertFalse($result);
    }

    public function test_group_clock_in_returns_false_when_clock_ins_are_too_old(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 3]);

        // 5 clock-ins but older than the 2-hour window
        for ($i = 0; $i < 5; $i++) {
            Attendance::create([
                'user_id'       => $user->id,
                'latitude'      => 3.1579,
                'longitude'     => 101.7123,
                'status'        => 'pending',
                'clock_in_time' => now()->subHours(3),
            ]);
        }

        $result = AttendanceVerificationService::verifyGroupClockIn(3.1579, 101.7123);

        $this->assertFalse($result);
    }

    public function test_group_clock_in_returns_false_when_no_coordinates_given(): void
    {
        $this->assertFalse(AttendanceVerificationService::verifyGroupClockIn(null, null));
        $this->assertFalse(AttendanceVerificationService::verifyGroupClockIn(0, 0));
    }
}
