<?php

namespace Tests\Feature;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Enums\Role;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\PublicHoliday;
use App\Models\User;
use App\Services\MonthlyTimesheetService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyTimesheetReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_and_admin_can_access_monthly_report_hub(): void
    {
        $hr = User::factory()->create(['role' => Role::HR->value, 'is_active' => true]);
        $admin = User::factory()->create(['role' => Role::SuperAdmin->value, 'is_active' => true]);

        $responseHr = $this->actingAs($hr)->get(route('attendance.monthly.index'));
        $responseHr->assertOk();
        $responseHr->assertSee('Monthly Payroll & Timesheet Hub');

        $responseAdmin = $this->actingAs($admin)->get(route('attendance.monthly.index'));
        $responseAdmin->assertOk();
    }

    public function test_manager_can_access_monthly_hub_and_only_sees_subordinates(): void
    {
        $manager = User::factory()->create(['role' => Role::Manager->value, 'is_active' => true]);
        $subordinate = User::factory()->create([
            'role' => Role::Staff->value,
            'name' => 'Subordinate Staff',
            'manager_id' => $manager->id,
            'is_active' => true,
        ]);
        $otherStaff = User::factory()->create([
            'role' => Role::Staff->value,
            'name' => 'Unrelated Staff',
            'manager_id' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($manager)->get(route('attendance.monthly.index'));
        $response->assertOk();
        $response->assertSee('Subordinate Staff');
        $response->assertDontSee('Unrelated Staff');
    }

    public function test_regular_staff_cannot_access_company_monthly_hub(): void
    {
        $staff = User::factory()->create(['role' => Role::Staff->value, 'is_active' => true]);

        $response = $this->actingAs($staff)->get(route('attendance.monthly.index'));
        $response->assertForbidden();
    }

    public function test_staff_can_view_own_monthly_slip(): void
    {
        $staff = User::factory()->create([
            'role' => Role::Staff->value,
            'name' => 'Self Check Staff',
            'is_active' => true,
        ]);

        $response = $this->actingAs($staff)->get(route('attendance.monthly.slip', [
            'user' => $staff->id,
            'month' => 9,
            'year' => 2026,
        ]));

        $response->assertOk();
        $response->assertSee('Monthly Attendance Slip');
        $response->assertSee('Self Check Staff');
    }

    public function test_staff_cannot_view_other_staff_monthly_slip(): void
    {
        $staffA = User::factory()->create(['role' => Role::Staff->value, 'is_active' => true]);
        $staffB = User::factory()->create(['role' => Role::Staff->value, 'is_active' => true]);

        $response = $this->actingAs($staffA)->get(route('attendance.monthly.slip', [
            'user' => $staffB->id,
            'month' => 9,
            'year' => 2026,
        ]));

        $response->assertForbidden();
    }

    public function test_manager_can_view_subordinate_slip_but_not_unrelated_staff(): void
    {
        $manager = User::factory()->create(['role' => Role::Manager->value, 'is_active' => true]);
        $subordinate = User::factory()->create([
            'role' => Role::Staff->value,
            'manager_id' => $manager->id,
            'is_active' => true,
        ]);
        $unrelated = User::factory()->create([
            'role' => Role::Staff->value,
            'manager_id' => null,
            'is_active' => true,
        ]);

        $responseAllowed = $this->actingAs($manager)->get(route('attendance.monthly.slip', [
            'user' => $subordinate->id,
            'month' => 9,
            'year' => 2026,
        ]));
        $responseAllowed->assertOk();

        $responseDenied = $this->actingAs($manager)->get(route('attendance.monthly.slip', [
            'user' => $unrelated->id,
            'month' => 9,
            'year' => 2026,
        ]));
        $responseDenied->assertForbidden();
    }

    public function test_monthly_metrics_calculation_regular_and_overtime(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 30, 23, 59, 59));

        $staff = User::factory()->create(['role' => Role::Staff->value, 'is_active' => true]);

        // Shift 1: 9 hours worked (8h regular, 1h overtime)
        Attendance::create([
            'user_id' => $staff->id,
            'site_name' => 'Site Alpha',
            'latitude' => 3.123,
            'longitude' => 101.456,
            'status' => 'approved',
            'clock_in_time' => Carbon::parse('2026-09-02 08:00:00'),
            'clock_out_time' => Carbon::parse('2026-09-02 17:00:00'),
        ]);

        // Shift 2: 10 hours worked (8h regular, 2h overtime)
        Attendance::create([
            'user_id' => $staff->id,
            'site_name' => 'Site Beta',
            'latitude' => 3.123,
            'longitude' => 101.456,
            'status' => 'completed',
            'clock_in_time' => Carbon::parse('2026-09-03 08:00:00'),
            'clock_out_time' => Carbon::parse('2026-09-03 18:00:00'),
        ]);

        // Approved Leave on 2026-09-04 (Annual Leave)
        LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => '2026-09-04',
            'end_date' => '2026-09-04',
            'days_count' => 1,
            'status' => LeaveStatus::Approved,
            'reason' => 'Rest day',
        ]);

        // Public Holiday on 2026-09-16 (Malaysia Day)
        PublicHoliday::create([
            'name' => 'Malaysia Day',
            'date' => '2026-09-16',
            'day_name' => 'Wednesday',
            'year' => 2026,
            'is_nationwide' => true,
        ]);

        $data = MonthlyTimesheetService::getUserMonthlyData($staff, 9, 2026);

        $this->assertEquals(2, $data['kpi']['days_present']);
        $this->assertEquals(16.0, $data['kpi']['total_regular_hours']);
        $this->assertEquals(3.0, $data['kpi']['total_overtime_hours']);
        $this->assertEquals(19.0, $data['kpi']['total_worked_hours']);
        $this->assertEquals(1.0, $data['kpi']['annual_leave_days']);
        $this->assertEquals(1.0, $data['kpi']['total_leave_days']);

        // Check specific day row
        $day16 = collect($data['days'])->firstWhere('day', 16);
        $this->assertTrue($day16['is_holiday']);
        $this->assertEquals('Malaysia Day', $day16['holiday_name']);
    }

    public function test_payroll_bundle_compiles_all_staff(): void
    {
        $admin = User::factory()->create(['role' => Role::SuperAdmin->value, 'is_active' => true]);
        $staff1 = User::factory()->create(['role' => Role::Staff->value, 'name' => 'Staff 1', 'is_active' => true]);
        $staff2 = User::factory()->create(['role' => Role::Staff->value, 'name' => 'Staff 2', 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('attendance.monthly.bundle', [
            'month' => 9,
            'year' => 2026,
        ]));

        $response->assertOk();
        $response->assertSee('Staff 1');
        $response->assertSee('Staff 2');
        $response->assertSee('TUMPAT SOLUTIONS SDN BHD');
    }

    public function test_monthly_summary_csv_export_streams_correct_data(): void
    {
        $admin = User::factory()->create(['role' => Role::SuperAdmin->value, 'is_active' => true]);
        $staff = User::factory()->create([
            'role' => Role::Staff->value,
            'name' => 'Export Staff',
            'phone' => '0129998888',
            'is_active' => true,
        ]);

        Attendance::create([
            'user_id' => $staff->id,
            'site_name' => 'HQ',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::parse('2026-09-05 08:00:00'),
            'clock_out_time' => Carbon::parse('2026-09-05 16:00:00'),
        ]);

        $response = $this->actingAs($admin)->get(route('attendance.monthly.csv', [
            'month' => 9,
            'year' => 2026,
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Employee Name', $csv);
        $this->assertStringContainsString('Export Staff', $csv);
        $this->assertStringContainsString('0129998888', $csv);
    }
}

