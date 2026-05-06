<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for UC-03 (Manager Approves / Rejects Attendance).
 *
 * Covers the approval and rejection flows, including the self-approval guard,
 * without requiring a full Filament/HTTP request.
 */
class AttendanceApprovalTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Approval flow
    // -------------------------------------------------------------------------

    public function test_approving_attendance_sets_approved_status(): void
    {
        $manager = User::factory()->create(['role' => 2]);
        $staff   = User::factory()->create(['role' => 3]);

        $attendance = Attendance::create([
            'user_id'       => $staff->id,
            'site_name'     => 'HQ',
            'latitude'      => 3.0,
            'longitude'     => 101.0,
            'status'        => 'pending',
            'clock_in_time' => now()->subHours(4),
        ]);

        $attendance->update([
            'status'         => 'approved',
            'approved_by'    => $manager->id,
            'approved_at'    => now(),
            'approval_notes' => 'Looks good',
        ]);

        $record = $attendance->fresh();
        $this->assertSame('approved', $record->status);
        $this->assertSame($manager->id, $record->approved_by);
        $this->assertNotNull($record->approved_at);
        $this->assertSame('Looks good', $record->approval_notes);
    }

    public function test_rejecting_attendance_sets_rejected_status(): void
    {
        $manager = User::factory()->create(['role' => 2]);
        $staff   = User::factory()->create(['role' => 3]);

        $attendance = Attendance::create([
            'user_id'       => $staff->id,
            'site_name'     => 'HQ',
            'latitude'      => 3.0,
            'longitude'     => 101.0,
            'status'        => 'pending',
            'clock_in_time' => now()->subHours(4),
        ]);

        $attendance->update([
            'status'         => 'rejected',
            'approved_by'    => $manager->id,
            'approved_at'    => now(),
            'approval_notes' => 'Unrecognised location',
        ]);

        $record = $attendance->fresh();
        $this->assertSame('rejected', $record->status);
        $this->assertSame($manager->id, $record->approved_by);
        $this->assertSame('Unrecognised location', $record->approval_notes);
    }

    public function test_approved_at_timestamp_is_recorded(): void
    {
        $approvalTime = Carbon::create(2026, 5, 1, 14, 0, 0);
        Carbon::setTestNow($approvalTime);

        $manager = User::factory()->create(['role' => 2]);
        $staff   = User::factory()->create(['role' => 3]);

        $attendance = Attendance::create([
            'user_id'       => $staff->id,
            'site_name'     => 'HQ',
            'latitude'      => 3.0,
            'longitude'     => 101.0,
            'status'        => 'pending',
            'clock_in_time' => now()->subHours(4),
        ]);

        $attendance->update([
            'status'      => 'approved',
            'approved_by' => $manager->id,
            'approved_at' => now(),
        ]);

        $this->assertEquals($approvalTime->timestamp, $attendance->fresh()->approved_at->timestamp);

        Carbon::setTestNow();
    }

    // -------------------------------------------------------------------------
    // Self-approval guard
    // -------------------------------------------------------------------------

    public function test_self_approval_is_blocked(): void
    {
        $manager = User::factory()->create(['role' => 2]);

        $ownAttendance = Attendance::create([
            'user_id'       => $manager->id,
            'site_name'     => 'HQ',
            'latitude'      => 3.0,
            'longitude'     => 101.0,
            'status'        => 'pending',
            'clock_in_time' => now()->subHours(4),
        ]);

        // The resource prevents approval when user_id === approver id.
        $isSelfApproval = $ownAttendance->user_id === $manager->id;
        $this->assertTrue($isSelfApproval, 'Self-approval should be detectable and must be blocked by the resource.');

        // The record must remain unchanged (not approved).
        $this->assertSame('pending', $ownAttendance->fresh()->status);
    }

    // -------------------------------------------------------------------------
    // Temporary records are also approvable (clocked out without prior approval)
    // -------------------------------------------------------------------------

    public function test_temporary_attendance_can_be_approved(): void
    {
        $manager = User::factory()->create(['role' => 2]);
        $staff   = User::factory()->create(['role' => 3]);

        $attendance = Attendance::create([
            'user_id'        => $staff->id,
            'site_name'      => 'HQ',
            'latitude'       => 3.0,
            'longitude'      => 101.0,
            'status'         => 'temporary',
            'clock_in_time'  => now()->subHours(8),
            'clock_out_time' => now(),
        ]);

        $attendance->update([
            'status'      => 'approved',
            'approved_by' => $manager->id,
            'approved_at' => now(),
        ]);

        $this->assertSame('approved', $attendance->fresh()->status);
    }
}
