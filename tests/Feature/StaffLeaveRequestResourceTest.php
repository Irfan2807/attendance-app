<?php

namespace Tests\Feature;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffLeaveRequestResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_own_leaves_list(): void
    {
        $staff1 = User::factory()->create(['role' => 3]);
        $staff2 = User::factory()->create(['role' => 3]);

        LeaveRequest::create([
            'user_id' => $staff1->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => '2026-09-20',
            'end_date' => '2026-09-20',
            'days_count' => 1.0,
            'reason' => 'Staff 1 personal matter',
            'status' => LeaveStatus::Pending,
        ]);

        LeaveRequest::create([
            'user_id' => $staff2->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-21',
            'end_date' => '2026-09-21',
            'days_count' => 1.0,
            'reason' => 'Staff 2 clinic visit',
            'status' => LeaveStatus::Pending,
        ]);

        $response = $this->actingAs($staff1)->get('/staff/my-leaves');

        $response->assertSuccessful();
        $response->assertSee('Staff 1 personal matter');
        $response->assertDontSee('Staff 2 clinic visit');
    }

    public function test_staff_cannot_access_manager_leave_approvals(): void
    {
        $staff = User::factory()->create(['role' => 3]);

        $response = $this->actingAs($staff)->get('/staff/leave-approvals');

        $response->assertForbidden();
    }

    public function test_manager_can_access_leave_approvals(): void
    {
        $manager = User::factory()->create(['role' => 2]);
        $staff = User::factory()->create(['role' => 3]);

        LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-22',
            'end_date' => '2026-09-22',
            'days_count' => 1.0,
            'reason' => 'Severe headache with MC',
            'status' => LeaveStatus::Pending,
        ]);

        $response = $this->actingAs($manager)->get('/staff/leave-approvals');

        $response->assertSuccessful();
        $response->assertSee($staff->name);
        $response->assertSee('Severe headache with MC');
    }

    public function test_peer_manager_leaves_excluded_from_approval_queue(): void
    {
        $manager1 = User::factory()->create(['name' => 'Manager One', 'role' => 2]);
        $manager2 = User::factory()->create(['name' => 'Manager Two', 'role' => 2]);

        LeaveRequest::create([
            'user_id' => $manager1->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => '2026-09-25',
            'end_date' => '2026-09-25',
            'days_count' => 1.0,
            'reason' => 'Manager 1 leave application',
            'status' => LeaveStatus::Pending,
        ]);

        $response = $this->actingAs($manager2)->get('/staff/leave-approvals');

        $response->assertSuccessful();
        $response->assertDontSee('Manager 1 leave application');
    }

    public function test_manager_can_approve_leave_request(): void
    {
        $manager = User::factory()->create(['role' => 2]);
        $staff = User::factory()->create(['role' => 3]);

        $leave = LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-22',
            'end_date' => '2026-09-22',
            'days_count' => 1.0,
            'status' => LeaveStatus::Pending,
        ]);

        $this->actingAs($manager);

        // Simulate approval update
        $leave->update([
            'status' => LeaveStatus::Approved,
            'actioned_by' => $manager->id,
            'actioned_at' => now(),
        ]);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'approved',
            'actioned_by' => $manager->id,
        ]);

        $this->assertTrue($staff->isOnApprovedLeave('2026-09-22'));
    }

    public function test_admin_can_access_company_wide_leave_requests(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create(['role' => 3]);

        LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => '2026-09-28',
            'end_date' => '2026-09-29',
            'days_count' => 2.0,
            'status' => LeaveStatus::Pending,
        ]);

        $response = $this->actingAs($admin)->get('/admin/leave-requests');

        $response->assertSuccessful();
        $response->assertSee($staff->name);
    }
}

