<?php

namespace Tests\Feature;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Enums\Role;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrRoleAndSubordinatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_role_helper_and_manager_relationship(): void
    {
        $manager = User::factory()->create(['role' => Role::Manager, 'name' => 'Supervisor Dave']);
        $staff = User::factory()->create([
            'role' => Role::Staff,
            'name' => 'Worker Alice',
            'manager_id' => $manager->id,
        ]);
        $hr = User::factory()->create(['role' => Role::HR, 'name' => 'HR Karen']);

        $this->assertTrue($hr->isHr());
        $this->assertTrue($hr->isManagerOrAdmin());
        $this->assertFalse($hr->isAdmin());
        $this->assertFalse($hr->isManager());

        $this->assertNotNull($staff->manager);
        $this->assertSame($manager->id, $staff->manager->id);
        $this->assertTrue($manager->subordinates->contains('id', $staff->id));

        $staffPanel = new Panel();
        $staffPanel->id('staff');
        $this->assertTrue($hr->canAccessPanel($staffPanel));
    }

    public function test_manager_only_sees_assigned_subordinates_in_clock_in_approval_queue(): void
    {
        $manager1 = User::factory()->create(['role' => Role::Manager, 'name' => 'Manager One']);
        $manager2 = User::factory()->create(['role' => Role::Manager, 'name' => 'Manager Two']);

        $staff1 = User::factory()->create(['role' => Role::Staff, 'name' => 'Subordinate One', 'manager_id' => $manager1->id]);
        $staff2 = User::factory()->create(['role' => Role::Staff, 'name' => 'Subordinate Two', 'manager_id' => $manager2->id]);

        Attendance::create([
            'user_id' => $staff1->id,
            'status' => 'pending',
            'site_name' => 'Site A',
            'latitude' => 3.1234,
            'longitude' => 101.5678,
            'clock_in_time' => now(),
        ]);

        Attendance::create([
            'user_id' => $staff2->id,
            'status' => 'pending',
            'site_name' => 'Site B',
            'latitude' => 3.1234,
            'longitude' => 101.5678,
            'clock_in_time' => now(),
        ]);

        // Manager 1 should only see Subordinate One
        $response1 = $this->actingAs($manager1)->get('/staff/clock-in-approvals');
        $response1->assertSuccessful();
        $response1->assertSee('Subordinate One');
        $response1->assertDontSee('Subordinate Two');

        // Manager 2 should only see Subordinate Two
        $response2 = $this->actingAs($manager2)->get('/staff/clock-in-approvals');
        $response2->assertSuccessful();
        $response2->assertSee('Subordinate Two');
        $response2->assertDontSee('Subordinate One');
    }

    public function test_hr_and_director_see_company_wide_clock_in_approvals(): void
    {
        $hr = User::factory()->create(['role' => Role::HR]);
        $manager = User::factory()->create(['role' => Role::Manager]);

        $staff1 = User::factory()->create(['role' => Role::Staff, 'name' => 'Worker Alpha', 'manager_id' => $manager->id]);
        $staff2 = User::factory()->create(['role' => Role::Staff, 'name' => 'Worker Beta', 'manager_id' => null]);

        Attendance::create([
            'user_id' => $staff1->id,
            'status' => 'pending',
            'site_name' => 'Site Alpha',
            'latitude' => 3.1234,
            'longitude' => 101.5678,
            'clock_in_time' => now(),
        ]);

        Attendance::create([
            'user_id' => $staff2->id,
            'status' => 'pending',
            'site_name' => 'Site Beta',
            'latitude' => 3.1234,
            'longitude' => 101.5678,
            'clock_in_time' => now(),
        ]);

        $response = $this->actingAs($hr)->get('/staff/clock-in-approvals');
        $response->assertSuccessful();
        $response->assertSee('Worker Alpha');
        $response->assertSee('Worker Beta');
    }

    public function test_manager_only_sees_assigned_subordinates_in_leave_approvals(): void
    {
        $manager1 = User::factory()->create(['role' => Role::Manager, 'name' => 'Manager John']);
        $manager2 = User::factory()->create(['role' => Role::Manager, 'name' => 'Manager Mark']);

        $staff1 = User::factory()->create(['role' => Role::Staff, 'name' => 'John Subordinate', 'manager_id' => $manager1->id]);
        $staff2 = User::factory()->create(['role' => Role::Staff, 'name' => 'Mark Subordinate', 'manager_id' => $manager2->id]);

        LeaveRequest::create([
            'user_id' => $staff1->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-01',
            'days_count' => 1.0,
            'reason' => 'John sub leave request',
            'status' => LeaveStatus::Pending,
        ]);

        LeaveRequest::create([
            'user_id' => $staff2->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-02',
            'days_count' => 1.0,
            'reason' => 'Mark sub leave request',
            'status' => LeaveStatus::Pending,
        ]);

        $response1 = $this->actingAs($manager1)->get('/staff/leave-approvals');
        $response1->assertSuccessful();
        $response1->assertSee('John sub leave request');
        $response1->assertDontSee('Mark sub leave request');

        $response2 = $this->actingAs($manager2)->get('/staff/leave-approvals');
        $response2->assertSuccessful();
        $response2->assertSee('Mark sub leave request');
        $response2->assertDontSee('John sub leave request');
    }

    public function test_hr_executive_can_see_and_approve_manager_and_staff_leaves(): void
    {
        $hr = User::factory()->create(['role' => Role::HR, 'name' => 'HR Specialist']);
        $manager = User::factory()->create(['role' => Role::Manager, 'name' => 'Manager Robert']);
        $staff = User::factory()->create(['role' => Role::Staff, 'name' => 'Staff Simon', 'manager_id' => $manager->id]);

        $managerLeave = LeaveRequest::create([
            'user_id' => $manager->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-11-05',
            'end_date' => '2026-11-06',
            'days_count' => 2.0,
            'reason' => 'Manager Robert clinic MC',
            'status' => LeaveStatus::Pending,
        ]);

        $staffLeave = LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-10',
            'days_count' => 1.0,
            'reason' => 'Staff Simon vacation',
            'status' => LeaveStatus::Pending,
        ]);

        $response = $this->actingAs($hr)->get('/staff/leave-approvals');
        $response->assertSuccessful();
        $response->assertSee('Manager Robert clinic MC');
        $response->assertSee('Staff Simon vacation');

        // HR approves Manager's leave
        $managerLeave->update([
            'status' => LeaveStatus::Approved,
            'actioned_by' => $hr->id,
            'actioned_at' => now(),
        ]);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $managerLeave->id,
            'status' => 'approved',
            'actioned_by' => $hr->id,
        ]);

        $this->assertTrue($manager->isOnApprovedLeave('2026-11-05'));
    }

    public function test_manager_sees_assigned_subordinates_in_staff_directory(): void
    {
        $manager1 = User::factory()->create(['role' => Role::Manager]);
        $manager2 = User::factory()->create(['role' => Role::Manager]);

        $staff1 = User::factory()->create(['role' => Role::Staff, 'name' => 'Assigned To M1', 'manager_id' => $manager1->id]);
        $staff2 = User::factory()->create(['role' => Role::Staff, 'name' => 'Assigned To M2', 'manager_id' => $manager2->id]);

        $response = $this->actingAs($manager1)->get('/staff/staff');
        $response->assertSuccessful();
        $response->assertSee('Assigned To M1');
        $response->assertDontSee('Assigned To M2');
    }
}
