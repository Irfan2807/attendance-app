<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceAccessIntegrationTest extends TestCase
{
    use RefreshDatabase;

    // ----------------------------------------------------------------
    // Guests must be redirected to login
    // ----------------------------------------------------------------

    public function test_guest_cannot_access_export_csv(): void
    {
        $response = $this->get('/attendance/export');

        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_print_view(): void
    {
        $response = $this->get('/attendance/print');

        $response->assertRedirect('/login');
    }

    // ----------------------------------------------------------------
    // Staff (role 3) must be blocked with 403
    // ----------------------------------------------------------------

    public function test_staff_cannot_export_csv(): void
    {
        /** @var User $staff */
        $staff = User::factory()->create(['role' => 3]);

        $response = $this->actingAs($staff)->get('/attendance/export');

        $response->assertStatus(403);
    }

    public function test_staff_cannot_access_print_view(): void
    {
        /** @var User $staff */
        $staff = User::factory()->create(['role' => 3]);

        $response = $this->actingAs($staff)->get('/attendance/print');

        $response->assertStatus(403);
    }

    // ----------------------------------------------------------------
    // Manager (role 2) can access both endpoints
    // ----------------------------------------------------------------

    public function test_manager_can_export_csv(): void
    {
        /** @var User $manager */
        $manager = User::factory()->create(['role' => 2]);

        $response = $this->actingAs($manager)->get('/attendance/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_manager_can_access_print_view(): void
    {
        /** @var User $manager */
        $manager = User::factory()->create(['role' => 2]);

        $response = $this->actingAs($manager)->get('/attendance/print');

        $response->assertStatus(200);
    }

    // ----------------------------------------------------------------
    // Admin (role 1) can access both endpoints
    // ----------------------------------------------------------------

    public function test_admin_can_export_csv(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->get('/attendance/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_access_print_view(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->get('/attendance/print');

        $response->assertStatus(200);
    }

    // ----------------------------------------------------------------
    // CSV export content
    // ----------------------------------------------------------------

    public function test_export_csv_includes_header_row(): void
    {
        /** @var User $manager */
        $manager = User::factory()->create(['role' => 2]);

        $response = $this->actingAs($manager)->get('/attendance/export');

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString('id,user,site_name', $content);
    }

    public function test_export_csv_includes_attendance_records(): void
    {
        /** @var User $manager */
        $manager = User::factory()->create(['role' => 2]);
        /** @var User $staff */
        $staff = User::factory()->create(['role' => 3, 'name' => 'John Doe']);

        Attendance::create([
            'user_id'       => $staff->id,
            'site_name'     => 'HQ',
            'latitude'      => 3.1390,
            'longitude'     => 101.6869,
            'status'        => 'verified',
            'clock_in_time' => Carbon::create(2026, 4, 20, 8, 0, 0),
        ]);

        $response = $this->actingAs($manager)->get('/attendance/export');

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString('John Doe', $content);
        $this->assertStringContainsString('HQ', $content);
    }
}
