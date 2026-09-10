<?php

namespace Tests\Feature;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Enums\Role;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveQuotaAndBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_default_quotas_and_initial_remaining_balance(): void
    {
        $user = User::factory()->create();

        $this->assertEquals(14.0, $user->annual_leave_quota);
        $this->assertEquals(14.0, $user->medical_leave_quota);
        $this->assertEquals(60.0, $user->hospitalization_quota);

        $this->assertEquals(14.0, $user->leaveQuota(LeaveType::AnnualLeave));
        $this->assertEquals(14.0, $user->leaveQuota(LeaveType::MedicalLeave));
        $this->assertEquals(60.0, $user->leaveQuota(LeaveType::Hospitalization));
        $this->assertNull($user->leaveQuota(LeaveType::UnpaidLeave));

        $this->assertEquals(14.0, $user->remainingLeave(LeaveType::AnnualLeave));
        $this->assertEquals(14.0, $user->remainingLeave(LeaveType::MedicalLeave));
        $this->assertEquals(60.0, $user->remainingLeave(LeaveType::Hospitalization));
        $this->assertNull($user->remainingLeave(LeaveType::UnpaidLeave));
    }

    public function test_approved_leave_deducts_from_remaining_balance(): void
    {
        $user = User::factory()->create();

        LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'days_count' => 2.5,
            'status' => LeaveStatus::Approved,
        ]);

        $this->assertEquals(2.5, $user->approvedLeaveTaken(LeaveType::AnnualLeave));
        $this->assertEquals(11.5, $user->remainingLeave(LeaveType::AnnualLeave));

        // MC is unaffected
        $this->assertEquals(0.0, $user->approvedLeaveTaken(LeaveType::MedicalLeave));
        $this->assertEquals(14.0, $user->remainingLeave(LeaveType::MedicalLeave));
    }

    public function test_pending_and_rejected_leaves_do_not_deduct_from_balance(): void
    {
        $user = User::factory()->create();

        LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'days_count' => 3.0,
            'status' => LeaveStatus::Pending,
        ]);

        LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'days_count' => 2.0,
            'status' => LeaveStatus::Rejected,
        ]);

        $this->assertEquals(0.0, $user->approvedLeaveTaken(LeaveType::AnnualLeave));
        $this->assertEquals(14.0, $user->remainingLeave(LeaveType::AnnualLeave));
    }

    public function test_leave_balances_isolated_by_calendar_year(): void
    {
        $user = User::factory()->create();

        // 4 days in 2025
        LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2025-05-10',
            'end_date' => '2025-05-13',
            'days_count' => 4.0,
            'status' => LeaveStatus::Approved,
        ]);

        // 2 days in 2026
        LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-16',
            'days_count' => 2.0,
            'status' => LeaveStatus::Approved,
        ]);

        $this->assertEquals(4.0, $user->approvedLeaveTaken(LeaveType::MedicalLeave, 2025));
        $this->assertEquals(10.0, $user->remainingLeave(LeaveType::MedicalLeave, 2025));

        $this->assertEquals(2.0, $user->approvedLeaveTaken(LeaveType::MedicalLeave, 2026));
        $this->assertEquals(12.0, $user->remainingLeave(LeaveType::MedicalLeave, 2026));
    }

    public function test_has_sufficient_leave_helper(): void
    {
        $user = User::factory()->create();

        // Initially 14 days
        $this->assertTrue($user->hasSufficientLeave(LeaveType::AnnualLeave, 14.0));
        $this->assertFalse($user->hasSufficientLeave(LeaveType::AnnualLeave, 14.5));

        // Unpaid leave is always sufficient
        $this->assertTrue($user->hasSufficientLeave(LeaveType::UnpaidLeave, 100.0));
    }

    public function test_custom_quota_can_be_assigned_to_senior_staff(): void
    {
        $seniorStaff = User::factory()->create([
            'annual_leave_quota' => 21.0,
            'medical_leave_quota' => 22.0,
        ]);

        $this->assertEquals(21.0, $seniorStaff->leaveQuota(LeaveType::AnnualLeave));
        $this->assertEquals(22.0, $seniorStaff->leaveQuota(LeaveType::MedicalLeave));

        $this->assertEquals(21.0, $seniorStaff->remainingLeave(LeaveType::AnnualLeave));
        $this->assertEquals(22.0, $seniorStaff->remainingLeave(LeaveType::MedicalLeave));
    }

    public function test_leave_balance_widget_renders_on_staff_my_leaves(): void
    {
        $staff = User::factory()->create(['role' => Role::Staff]);

        LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'days_count' => 3.0,
            'status' => LeaveStatus::Approved,
        ]);

        $this->actingAs($staff);

        \Livewire\Livewire::test(\App\Filament\Staff\Widgets\StaffLeaveBalanceWidget::class)
            ->assertSee('Annual Leave')
            ->assertSee('11 / 14 Days')
            ->assertSee('Medical Leave (MC)')
            ->assertSee('14 / 14 Days')
            ->assertSee('Hospitalization')
            ->assertSee('60 / 60 Days');

        $response = $this->get('/staff/my-leaves');
        $response->assertSuccessful();
    }
}
