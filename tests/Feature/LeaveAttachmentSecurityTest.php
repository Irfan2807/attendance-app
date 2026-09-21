<?php

namespace Tests\Feature;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Enums\Role;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeaveAttachmentSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_unauthenticated_guest_cannot_access_attachment(): void
    {
        $staff = User::factory()->create(['role' => Role::Staff]);
        $leave = LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-20',
            'end_date' => '2026-09-20',
            'days_count' => 1.0,
            'attachment_path' => 'leave-attachments/test_mc.pdf',
            'status' => LeaveStatus::Pending,
        ]);

        $response = $this->get(route('leaves.attachment', $leave));
        $response->assertRedirect('/login');
    }

    public function test_applicant_can_access_own_medical_certificate(): void
    {
        $staff = User::factory()->create(['role' => Role::Staff]);
        Storage::disk('local')->put('leave-attachments/my_doctor_mc.pdf', 'dummy content');

        $leave = LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-20',
            'end_date' => '2026-09-20',
            'days_count' => 1.0,
            'attachment_path' => 'leave-attachments/my_doctor_mc.pdf',
            'status' => LeaveStatus::Pending,
        ]);

        $response = $this->actingAs($staff)->get(route('leaves.attachment', $leave));
        $response->assertOk();
    }

    public function test_assigned_manager_can_access_subordinate_medical_certificate(): void
    {
        $manager = User::factory()->create(['role' => Role::Manager]);
        $subordinate = User::factory()->create([
            'role' => Role::Staff,
            'manager_id' => $manager->id,
        ]);

        Storage::disk('local')->put('leave-attachments/subordinate_mc.png', 'image bytes');

        $leave = LeaveRequest::create([
            'user_id' => $subordinate->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-20',
            'end_date' => '2026-09-20',
            'days_count' => 1.0,
            'attachment_path' => 'leave-attachments/subordinate_mc.png',
            'status' => LeaveStatus::Pending,
        ]);

        $response = $this->actingAs($manager)->get(route('leaves.attachment', $leave));
        $response->assertOk();
    }

    public function test_unrelated_manager_cannot_access_unassigned_staff_medical_certificate(): void
    {
        $managerA = User::factory()->create(['role' => Role::Manager]);
        $managerB = User::factory()->create(['role' => Role::Manager]);
        $staff = User::factory()->create([
            'role' => Role::Staff,
            'manager_id' => $managerA->id,
        ]);

        Storage::disk('local')->put('leave-attachments/private_mc.pdf', 'confidential data');

        $leave = LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-20',
            'end_date' => '2026-09-20',
            'days_count' => 1.0,
            'attachment_path' => 'leave-attachments/private_mc.pdf',
            'status' => LeaveStatus::Pending,
        ]);

        // Manager B attempts to view staff assigned to Manager A
        $response = $this->actingAs($managerB)->get(route('leaves.attachment', $leave));
        $response->assertForbidden();
    }

    public function test_peer_staff_cannot_access_another_staff_medical_certificate(): void
    {
        $staffA = User::factory()->create(['role' => Role::Staff]);
        $staffB = User::factory()->create(['role' => Role::Staff]);

        Storage::disk('local')->put('leave-attachments/staff_a_mc.pdf', 'confidential medical file');

        $leave = LeaveRequest::create([
            'user_id' => $staffA->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-20',
            'end_date' => '2026-09-20',
            'days_count' => 1.0,
            'attachment_path' => 'leave-attachments/staff_a_mc.pdf',
            'status' => LeaveStatus::Pending,
        ]);

        // Staff B attempts to view Staff A's medical certificate
        $response = $this->actingAs($staffB)->get(route('leaves.attachment', $leave));
        $response->assertForbidden();
    }

    public function test_hr_executive_can_access_medical_certificate(): void
    {
        $hr = User::factory()->create(['role' => Role::HR]);
        $staff = User::factory()->create(['role' => Role::Staff]);

        Storage::disk('local')->put('leave-attachments/hr_audit_mc.pdf', 'file content');

        $leave = LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-20',
            'end_date' => '2026-09-20',
            'days_count' => 1.0,
            'attachment_path' => 'leave-attachments/hr_audit_mc.pdf',
            'status' => LeaveStatus::Pending,
        ]);

        $response = $this->actingAs($hr)->get(route('leaves.attachment', $leave));
        $response->assertOk();
    }

    public function test_super_admin_can_access_medical_certificate(): void
    {
        $admin = User::factory()->create(['role' => Role::SuperAdmin]);
        $staff = User::factory()->create(['role' => Role::Staff]);

        Storage::disk('local')->put('leave-attachments/admin_review_mc.pdf', 'file content');

        $leave = LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-20',
            'end_date' => '2026-09-20',
            'days_count' => 1.0,
            'attachment_path' => 'leave-attachments/admin_review_mc.pdf',
            'status' => LeaveStatus::Pending,
        ]);

        $response = $this->actingAs($admin)->get(route('leaves.attachment', $leave));
        $response->assertOk();
    }

    public function test_supports_legacy_public_disk_files(): void
    {
        $staff = User::factory()->create(['role' => Role::Staff]);
        // Put on public disk instead of local
        Storage::disk('public')->put('leave-attachments/legacy_mc.jpg', 'legacy file bytes');

        $leave = LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-20',
            'end_date' => '2026-09-20',
            'days_count' => 1.0,
            'attachment_path' => 'leave-attachments/legacy_mc.jpg',
            'status' => LeaveStatus::Pending,
        ]);

        $response = $this->actingAs($staff)->get(route('leaves.attachment', $leave));
        $response->assertOk();
    }

    public function test_returns_404_when_leave_has_no_attachment_or_missing_file(): void
    {
        $staff = User::factory()->create(['role' => Role::Staff]);

        $leaveNoPath = LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => '2026-09-20',
            'end_date' => '2026-09-20',
            'days_count' => 1.0,
            'attachment_path' => null,
            'status' => LeaveStatus::Pending,
        ]);

        $response1 = $this->actingAs($staff)->get(route('leaves.attachment', $leaveNoPath));
        $response1->assertNotFound();

        $leaveMissingFile = LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-20',
            'end_date' => '2026-09-20',
            'days_count' => 1.0,
            'attachment_path' => 'leave-attachments/ghost_file.pdf',
            'status' => LeaveStatus::Pending,
        ]);

        $response2 = $this->actingAs($staff)->get(route('leaves.attachment', $leaveMissingFile));
        $response2->assertNotFound();
    }
}
