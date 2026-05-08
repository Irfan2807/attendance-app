<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceExportFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_export_applies_status_and_date_filters(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 8, 10, 0, 0));

        $admin = User::factory()->create(['role' => 1]);
        $staffA = User::factory()->create(['role' => 3, 'name' => 'Alice Staff']);
        $staffB = User::factory()->create(['role' => 3, 'name' => 'Bob Staff']);

        Attendance::create([
            'user_id' => $staffA->id,
            'site_name' => 'HQ',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::parse('2026-05-07 09:00:00'),
            'clock_out_time' => Carbon::parse('2026-05-07 18:00:00'),
        ]);

        Attendance::create([
            'user_id' => $staffB->id,
            'site_name' => 'HQ',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'pending',
            'clock_in_time' => Carbon::parse('2026-05-07 09:30:00'),
        ]);

        Attendance::create([
            'user_id' => $staffA->id,
            'site_name' => 'Site B',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::parse('2026-05-08 09:00:00'),
            'clock_out_time' => Carbon::parse('2026-05-08 17:00:00'),
        ]);

        $response = $this->actingAs($admin)->get(route('attendance.export', [
            'status' => 'approved',
            'from' => '2026-05-07',
            'until' => '2026-05-07',
        ]));

        $response->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringContainsString('Alice Staff', $csv);
        $this->assertStringNotContainsString('Bob Staff', $csv);
        $this->assertStringNotContainsString('Site B', $csv);

        Carbon::setTestNow();
    }

    public function test_csv_export_applies_user_and_site_filters(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $staffA = User::factory()->create(['role' => 3, 'name' => 'Charlie']);
        $staffB = User::factory()->create(['role' => 3, 'name' => 'Dana']);

        Attendance::create([
            'user_id' => $staffA->id,
            'site_name' => 'Site Alpha',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::parse('2026-05-06 08:00:00'),
        ]);

        Attendance::create([
            'user_id' => $staffB->id,
            'site_name' => 'Site Beta',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::parse('2026-05-06 08:00:00'),
        ]);

        $response = $this->actingAs($admin)->get(route('attendance.export', [
            'user_id' => $staffA->id,
            'site_name' => 'Site Alpha',
        ]));

        $response->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringContainsString('Charlie', $csv);
        $this->assertStringContainsString('Site Alpha', $csv);
        $this->assertStringNotContainsString('Dana', $csv);
        $this->assertStringNotContainsString('Site Beta', $csv);
    }

    public function test_csv_export_orders_rows_by_latest_clock_in_time(): void
    {
        $manager = User::factory()->create(['role' => 2]);
        $older = User::factory()->create(['role' => 3, 'name' => 'Older Shift']);
        $newer = User::factory()->create(['role' => 3, 'name' => 'Newer Shift']);

        Attendance::create([
            'user_id' => $older->id,
            'site_name' => 'HQ',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::parse('2026-05-07 08:00:00'),
        ]);

        Attendance::create([
            'user_id' => $newer->id,
            'site_name' => 'HQ',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::parse('2026-05-07 10:00:00'),
        ]);

        $response = $this->actingAs($manager)->get(route('attendance.export'));
        $response->assertOk();

        $csv = $response->streamedContent();
        $newerPos = strpos($csv, 'Newer Shift');
        $olderPos = strpos($csv, 'Older Shift');

        $this->assertNotFalse($newerPos);
        $this->assertNotFalse($olderPos);
        $this->assertLessThan($olderPos, $newerPos);
    }

    public function test_csv_export_is_forbidden_for_staff_role(): void
    {
        $staff = User::factory()->create(['role' => 3]);

        $this->actingAs($staff)
            ->get(route('attendance.export'))
            ->assertForbidden();
    }
}
