<?php

namespace Tests\Feature;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeaveRequestModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_leave_request_can_be_created_with_relations_and_casts(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $manager = User::factory()->create(['name' => 'Manager Jane', 'role' => 2]);

        $leave = LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-16',
            'days_count' => 2.0,
            'reason' => 'Severe fever and clinic consultation',
            'attachment_path' => 'leave-attachments/mc_test.jpg',
            'status' => LeaveStatus::Approved,
            'actioned_by' => $manager->id,
            'actioned_at' => now(),
        ]);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'user_id' => $user->id,
            'leave_type' => 'mc',
            'status' => 'approved',
            'days_count' => 2.0,
            'attachment_path' => 'leave-attachments/mc_test.jpg',
        ]);

        $this->assertTrue($leave->user->is($user));
        $this->assertTrue($leave->actionedBy->is($manager));
        $this->assertInstanceOf(Carbon::class, $leave->start_date);
        $this->assertInstanceOf(Carbon::class, $leave->end_date);
        $this->assertEquals(LeaveType::MedicalLeave, $leave->leave_type);
        $this->assertEquals(LeaveStatus::Approved, $leave->status);
        $this->assertTrue($leave->isApproved());
        $this->assertFalse($leave->isPending());
        $this->assertTrue($leave->isMedicalCertificate());
    }

    public function test_user_is_on_approved_leave_helper(): void
    {
        $user = User::factory()->create();

        // 1. No leave requests
        $this->assertFalse($user->isOnApprovedLeave('2026-09-15'));

        // 2. Pending leave should NOT count as on approved leave
        $pendingLeave = LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-17',
            'days_count' => 3.0,
            'status' => LeaveStatus::Pending,
        ]);

        $this->assertFalse($user->isOnApprovedLeave('2026-09-16'));

        // 3. Approved leave covers the start date, end date, and middle date
        $pendingLeave->update(['status' => LeaveStatus::Approved]);

        $this->assertTrue($user->isOnApprovedLeave('2026-09-15'));
        $this->assertTrue($user->isOnApprovedLeave('2026-09-16'));
        $this->assertTrue($user->isOnApprovedLeave('2026-09-17'));
        $this->assertFalse($user->isOnApprovedLeave('2026-09-14')); // Day before
        $this->assertFalse($user->isOnApprovedLeave('2026-09-18')); // Day after

        // 4. Rejected leave does not count
        $pendingLeave->update(['status' => LeaveStatus::Rejected]);
        $this->assertFalse($user->isOnApprovedLeave('2026-09-16'));
    }

    public function test_leave_type_enums_and_attachment_requirement(): void
    {
        $this->assertTrue(LeaveType::MedicalLeave->requiresAttachment());
        $this->assertTrue(LeaveType::Hospitalization->requiresAttachment());
        $this->assertFalse(LeaveType::AnnualLeave->requiresAttachment());
        $this->assertFalse(LeaveType::UnpaidLeave->requiresAttachment());

        $this->assertEquals('Medical Leave (MC)', LeaveType::MedicalLeave->label());
        $this->assertArrayHasKey('mc', LeaveType::options());
        $this->assertArrayHasKey('annual', LeaveType::options());
    }

    public function test_attachment_url_generation(): void
    {
        $user = User::factory()->create();

        $leaveWithAttachment = LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-20',
            'end_date' => '2026-09-20',
            'days_count' => 1.0,
            'attachment_path' => 'leave-attachments/doctor_mc.pdf',
            'status' => LeaveStatus::Pending,
        ]);

        $this->assertNotNull($leaveWithAttachment->attachment_url);
        $this->assertStringContainsString('leave-attachments/doctor_mc.pdf', $leaveWithAttachment->attachment_url);

        $leaveWithoutAttachment = LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => '2026-09-21',
            'end_date' => '2026-09-21',
            'days_count' => 1.0,
            'attachment_path' => null,
            'status' => LeaveStatus::Pending,
        ]);

        $this->assertNull($leaveWithoutAttachment->attachment_url);
    }

    public function test_leave_scopes(): void
    {
        $user = User::factory()->create();

        $pending = LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => '2026-09-20',
            'end_date' => '2026-09-22',
            'status' => LeaveStatus::Pending,
        ]);

        $approved = LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-21',
            'end_date' => '2026-09-21',
            'status' => LeaveStatus::Approved,
        ]);

        $this->assertEquals(1, LeaveRequest::pending()->count());
        $this->assertTrue(LeaveRequest::pending()->first()->is($pending));

        $this->assertEquals(1, LeaveRequest::approved()->count());
        $this->assertTrue(LeaveRequest::approved()->first()->is($approved));

        $activeOn21st = LeaveRequest::activeOnDate('2026-09-21')->get();
        $this->assertCount(1, $activeOn21st);
        $this->assertTrue($activeOn21st->first()->is($approved));
    }
}

