<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceInfraction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoClockOutSafetyNetTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Shifts that ARE within threshold — must not be touched
    // -------------------------------------------------------------------------

    public function test_shift_under_threshold_is_not_auto_clocked_out(): void
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'site_name' => 'HQ',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'pending',
            'clock_in_time' => now()->subHours(9)->subMinutes(59),
        ]);

        $this->artisan('attendance:auto-clock-out')->assertSuccessful();

        $attendance->refresh();
        $this->assertNull($attendance->clock_out_time);
        $this->assertSame('pending', $attendance->status);
        $this->assertDatabaseCount('attendance_infractions', 0);
    }

    // -------------------------------------------------------------------------
    // Shifts that EXCEED the threshold — must be auto-clocked out
    // -------------------------------------------------------------------------

    public function test_shift_at_exactly_threshold_is_auto_clocked_out(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 1, 20, 0, 0));
        $user = User::factory()->create(['incomplete_clock_out_count' => 0]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'site_name' => 'HQ',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'pending',
            'clock_in_time' => now()->subHours(10),
        ]);

        $this->artisan('attendance:auto-clock-out')->assertSuccessful();

        $attendance->refresh();
        $this->assertNotNull($attendance->clock_out_time);
        $this->assertSame('temporary', $attendance->status);

        Carbon::setTestNow();
    }

    public function test_shift_over_threshold_is_auto_clocked_out(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 1, 20, 0, 0));
        $user = User::factory()->create(['incomplete_clock_out_count' => 0]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'site_name' => 'HQ',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'pending',
            'clock_in_time' => now()->subHours(12),
        ]);

        $this->artisan('attendance:auto-clock-out')->assertSuccessful();

        $attendance->refresh();
        $this->assertNotNull($attendance->clock_out_time);
        $this->assertSame('temporary', $attendance->status);

        Carbon::setTestNow();
    }

    // -------------------------------------------------------------------------
    // clock_out_time is set to "now"
    // -------------------------------------------------------------------------

    public function test_clock_out_time_is_set_to_now(): void
    {
        $now = Carbon::create(2026, 5, 1, 20, 0, 0);
        Carbon::setTestNow($now);
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'site_name' => 'HQ',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'pending',
            'clock_in_time' => $now->copy()->subHours(11),
        ]);

        $this->artisan('attendance:auto-clock-out')->assertSuccessful();

        $attendance->refresh();
        $this->assertEquals($now->timestamp, $attendance->clock_out_time->timestamp);

        Carbon::setTestNow();
    }

    // -------------------------------------------------------------------------
    // Infraction is created
    // -------------------------------------------------------------------------

    public function test_infraction_is_created_for_auto_clocked_out_shift(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 1, 20, 0, 0));
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'site_name' => 'HQ',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'pending',
            'clock_in_time' => now()->subHours(11),
        ]);

        $this->artisan('attendance:auto-clock-out')->assertSuccessful();

        $this->assertDatabaseCount('attendance_infractions', 1);
        $infraction = AttendanceInfraction::first();
        $this->assertSame($user->id, $infraction->user_id);
        $this->assertSame($attendance->id, $infraction->attendance_id);

        Carbon::setTestNow();
    }

    public function test_infraction_type_contains_elapsed_hours(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 1, 20, 0, 0));
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'site_name' => 'HQ',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'pending',
            'clock_in_time' => now()->subHours(13),
        ]);

        $this->artisan('attendance:auto-clock-out')->assertSuccessful();

        $infraction = AttendanceInfraction::first();
        $this->assertSame('auto_clock_out_13', $infraction->infraction_type);

        Carbon::setTestNow();
    }

    public function test_infraction_auto_clock_out_time_matches_now(): void
    {
        $now = Carbon::create(2026, 5, 1, 20, 0, 0);
        Carbon::setTestNow($now);
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'site_name' => 'HQ',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'pending',
            'clock_in_time' => $now->copy()->subHours(11),
        ]);

        $this->artisan('attendance:auto-clock-out')->assertSuccessful();

        $infraction = AttendanceInfraction::first();
        $this->assertEquals($now->timestamp, $infraction->auto_clock_out_time->timestamp);

        Carbon::setTestNow();
    }

    public function test_infraction_notes_are_set(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 1, 20, 0, 0));
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'site_name' => 'HQ',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'pending',
            'clock_in_time' => now()->subHours(11),
        ]);

        $this->artisan('attendance:auto-clock-out')->assertSuccessful();

        $infraction = AttendanceInfraction::first();
        $this->assertSame('Auto clocked out after exceeding max shift length', $infraction->notes);

        Carbon::setTestNow();
    }

    // -------------------------------------------------------------------------
    // User counter is incremented
    // -------------------------------------------------------------------------

    public function test_user_incomplete_clock_out_count_is_incremented(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 1, 20, 0, 0));
        $user = User::factory()->create(['incomplete_clock_out_count' => 2]);

        Attendance::create([
            'user_id' => $user->id,
            'site_name' => 'HQ',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'pending',
            'clock_in_time' => now()->subHours(11),
        ]);

        $this->artisan('attendance:auto-clock-out')->assertSuccessful();

        $this->assertSame(3, $user->fresh()->incomplete_clock_out_count);

        Carbon::setTestNow();
    }

    // -------------------------------------------------------------------------
    // Already-clocked-out shifts are not re-processed
    // -------------------------------------------------------------------------

    public function test_already_clocked_out_shift_is_not_reprocessed(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 1, 20, 0, 0));
        $user = User::factory()->create(['incomplete_clock_out_count' => 0]);

        // Shift was already clocked out manually
        Attendance::create([
            'user_id' => $user->id,
            'site_name' => 'HQ',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'verified',
            'clock_in_time' => now()->subHours(11),
            'clock_out_time' => now()->subHours(1),
        ]);

        $this->artisan('attendance:auto-clock-out')->assertSuccessful();

        $this->assertDatabaseCount('attendance_infractions', 0);
        $this->assertSame(0, $user->fresh()->incomplete_clock_out_count);

        Carbon::setTestNow();
    }

    // -------------------------------------------------------------------------
    // Multiple long-running shifts are all handled
    // -------------------------------------------------------------------------

    public function test_all_long_running_shifts_are_processed(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 1, 20, 0, 0));

        $users = User::factory()->count(3)->create(['incomplete_clock_out_count' => 0]);

        foreach ($users as $user) {
            Attendance::create([
                'user_id' => $user->id,
                'site_name' => 'HQ',
                'latitude' => 3.0,
                'longitude' => 101.0,
                'status' => 'pending',
                'clock_in_time' => now()->subHours(11),
            ]);
        }

        $this->artisan('attendance:auto-clock-out')->assertSuccessful();

        $this->assertDatabaseCount('attendance_infractions', 3);
        foreach ($users as $user) {
            $this->assertSame(1, $user->fresh()->incomplete_clock_out_count);
        }

        Carbon::setTestNow();
    }

    // -------------------------------------------------------------------------
    // No-op when there are no long-running shifts
    // -------------------------------------------------------------------------

    public function test_no_op_when_no_long_running_shifts_exist(): void
    {
        $user = User::factory()->create();

        // Recent shift — not yet over threshold
        Attendance::create([
            'user_id' => $user->id,
            'site_name' => 'HQ',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'pending',
            'clock_in_time' => now()->subHours(5),
        ]);

        $this->artisan('attendance:auto-clock-out')->assertSuccessful();

        $this->assertDatabaseCount('attendance_infractions', 0);
    }
}
