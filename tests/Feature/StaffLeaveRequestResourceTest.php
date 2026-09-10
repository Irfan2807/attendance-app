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

    public function test_director_can_see_and_approve_manager_leave_in_admin_panel(): void
    {
        $director = User::factory()->create(['name' => 'Director HR', 'role' => 1]);
        $manager = User::factory()->create(['name' => 'Operations Manager', 'role' => 2]);

        $leave = LeaveRequest::create([
            'user_id' => $manager->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-02',
            'days_count' => 2.0,
            'reason' => 'Manager medical leave with hospital slip',
            'status' => LeaveStatus::Pending,
        ]);

        $response = $this->actingAs($director)->get('/admin/leave-requests');
        $response->assertSuccessful();
        $response->assertSee('Operations Manager');

        // Approve leave
        $leave->update([
            'status' => LeaveStatus::Approved,
            'actioned_by' => $director->id,
            'actioned_at' => now(),
        ]);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'approved',
            'actioned_by' => $director->id,
        ]);

        $this->assertTrue($manager->isOnApprovedLeave('2026-10-01'));
    }

    public function test_director_can_see_manager_leave_in_staff_approval_portal_while_peer_manager_cannot(): void
    {
        $director = User::factory()->create(['name' => 'Company Director', 'role' => 1]);
        $manager1 = User::factory()->create(['name' => 'Site Manager A', 'role' => 2]);
        $manager2 = User::factory()->create(['name' => 'Site Manager B', 'role' => 2]);

        LeaveRequest::create([
            'user_id' => $manager1->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-10',
            'days_count' => 1.0,
            'reason' => 'Manager A urgent family leave',
            'status' => LeaveStatus::Pending,
        ]);

        // Peer manager cannot see Manager A's leave
        $peerResponse = $this->actingAs($manager2)->get('/staff/leave-approvals');
        $peerResponse->assertSuccessful();
        $peerResponse->assertDontSee('Manager A urgent family leave');

        // Director can see Manager A's leave
        $directorResponse = $this->actingAs($director)->get('/staff/leave-approvals');
        $directorResponse->assertSuccessful();
        $directorResponse->assertSee('Manager A urgent family leave');
    }
}

