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

    public function test_print_view_renders_successfully_for_admin_and_manager(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $manager = User::factory()->create(['role' => 2]);
        $staff = User::factory()->create(['role' => 3, 'name' => 'Test Employee']);

        Attendance::create([
            'user_id' => $staff->id,
            'site_name' => 'HQ Site',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::parse('2026-05-08 09:00:00'),
            'clock_out_time' => Carbon::parse('2026-05-08 17:00:00'),
        ]);

        // Admin access
        $responseAdmin = $this->actingAs($admin)->get(route('attendance.print'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Attendance Report');
        $responseAdmin->assertSee('Test Employee');
        $responseAdmin->assertSee('HQ Site');

        // Manager access
        $responseManager = $this->actingAs($manager)->get(route('attendance.print'));
        $responseManager->assertOk();
        $responseManager->assertSee('Attendance Report');
    }

    public function test_print_view_forbidden_for_staff_and_redirects_guest(): void
    {
        $staff = User::factory()->create(['role' => 3]);

        // Guest is redirected
        $guestResponse = $this->get(route('attendance.print'));
        $guestResponse->assertRedirect('/login');

        // Staff is forbidden (403)
        $staffResponse = $this->actingAs($staff)->get(route('attendance.print'));
        $staffResponse->assertForbidden();
    }
}
