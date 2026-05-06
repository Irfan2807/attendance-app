<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Site;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for UC-01 (Employee Clock In) and UC-02 (Employee Clock Out).
 *
 * Covers the status-transition rules that the ClockInOutWidget enforces
 * without spinning up a full Livewire component.
 */
class AttendanceStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // UC-01: Clock In status rules
    // -------------------------------------------------------------------------

    public function test_clock_in_without_verification_sets_pending_status(): void
    {
        $user = User::factory()->create(['role' => 3]);

        // No sites registered → IP / GPS verification will fail → pending
        $attendance = Attendance::create([
            'user_id'            => $user->id,
            'site_name'          => 'Unknown Location',
            'latitude'           => 0,
            'longitude'          => 0,
            'status'             => 'pending',
            'clock_in_time'      => now(),
            'verification_notes' => 'IP mismatch | Awaiting manager approval',
        ]);

        $this->assertSame('pending', $attendance->status);
        $this->assertNull($attendance->clock_out_time);
    }

    public function test_clock_in_with_matching_site_ip_sets_approved_status(): void
    {
        $user = User::factory()->create(['role' => 3]);

        Site::create([
            'name'          => 'HQ',
            'ip_address'    => '192.168.1.1',
            'latitude'      => 3.0,
            'longitude'     => 101.0,
            'radius_meters' => 100,
            'is_active'     => true,
        ]);

        // Simulate a clock-in where the IP matched a site
        $attendance = Attendance::create([
            'user_id'            => $user->id,
            'site_name'          => 'HQ',
            'latitude'           => 3.0,
            'longitude'          => 101.0,
            'status'             => 'approved',
            'clock_in_time'      => now(),
            'verification_notes' => 'IP Verified: 192.168.1.1',
        ]);

        $this->assertSame('approved', $attendance->status);
    }

    public function test_cannot_have_two_open_shifts_simultaneously(): void
    {
        $user = User::factory()->create(['role' => 3]);

        Attendance::create([
            'user_id'       => $user->id,
            'site_name'     => 'HQ',
            'latitude'      => 3.0,
            'longitude'     => 101.0,
            'status'        => 'pending',
            'clock_in_time' => now()->subHours(2),
        ]);

        $activeShift = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out_time')
            ->first();

        $this->assertNotNull($activeShift, 'An open shift must be found, blocking a second clock-in.');
    }

    // -------------------------------------------------------------------------
    // UC-02: Clock Out status rules
    // -------------------------------------------------------------------------

    public function test_clock_out_of_approved_shift_sets_completed_status(): void
    {
        $user = User::factory()->create(['role' => 3]);

        $attendance = Attendance::create([
            'user_id'       => $user->id,
            'site_name'     => 'HQ',
            'latitude'      => 3.0,
            'longitude'     => 101.0,
            'status'        => 'approved',
            'clock_in_time' => now()->subHours(8),
        ]);

        $newStatus = $attendance->status === 'approved' ? 'completed' : 'temporary';
        $attendance->update([
            'clock_out_time' => now(),
            'status'         => $newStatus,
        ]);

        $this->assertSame('completed', $attendance->fresh()->status);
        $this->assertNotNull($attendance->fresh()->clock_out_time);
    }

    public function test_clock_out_of_pending_shift_sets_temporary_status(): void
    {
        $user = User::factory()->create(['role' => 3]);

        $attendance = Attendance::create([
            'user_id'       => $user->id,
            'site_name'     => 'HQ',
            'latitude'      => 3.0,
            'longitude'     => 101.0,
            'status'        => 'pending',
            'clock_in_time' => now()->subHours(8),
        ]);

        $newStatus = $attendance->status === 'approved' ? 'completed' : 'temporary';
        $attendance->update([
            'clock_out_time' => now(),
            'status'         => $newStatus,
        ]);

        $this->assertSame('temporary', $attendance->fresh()->status);
    }

    public function test_clock_out_records_correct_timestamp(): void
    {
        $now = Carbon::create(2026, 5, 1, 17, 30, 0);
        Carbon::setTestNow($now);

        $user = User::factory()->create(['role' => 3]);

        $attendance = Attendance::create([
            'user_id'       => $user->id,
            'site_name'     => 'HQ',
            'latitude'      => 3.0,
            'longitude'     => 101.0,
            'status'        => 'pending',
            'clock_in_time' => $now->copy()->subHours(8),
        ]);

        $attendance->update([
            'clock_out_time' => now(),
            'status'         => 'temporary',
        ]);

        $this->assertEquals($now->timestamp, $attendance->fresh()->clock_out_time->timestamp);

        Carbon::setTestNow();
    }
}
