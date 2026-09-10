<?php

namespace Tests\Feature;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AttendanceAnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveAttendanceAnalyticsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_rate_includes_on_leave_metrics_and_adjusted_rate(): void
    {
        $start = Carbon::create(2026, 9, 15, 6, 0, 0);
        $end = Carbon::create(2026, 9, 16, 6, 0, 0);

        // 3 active staff members
        $staffPresent = User::factory()->create(['role' => 3, 'is_active' => true, 'name' => 'Present Worker']);
        $staffOnLeave = User::factory()->create(['role' => 3, 'is_active' => true, 'name' => 'Sick Worker']);
        $staffAbsent = User::factory()->create(['role' => 3, 'is_active' => true, 'name' => 'Absent Worker']);

        // 1 present worker clocked in
        Attendance::create([
            'user_id' => $staffPresent->id,
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::create(2026, 9, 15, 8, 30, 0),
        ]);

        // 1 worker on approved MC
        LeaveRequest::create([
            'user_id' => $staffOnLeave->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-16',
            'days_count' => 2.0,
            'status' => LeaveStatus::Approved,
        ]);

        $data = AttendanceAnalyticsService::attendanceRate($start, $end);

        $this->assertSame(3, $data['total']);
        $this->assertSame(1, $data['present']);
        $this->assertSame(1, $data['on_leave']);
        $this->assertSame(2, $data['expected']); // 3 total - 1 on leave = 2 expected
        $this->assertSame(33.3, $data['rate']); // 1 / 3 = 33.3% raw attendance
        $this->assertSame(50.0, $data['adjusted_rate']); // 1 / 2 = 50.0% adjusted attendance for expected workforce
    }

    public function test_pending_leave_does_not_count_as_on_leave_in_attendance_rate(): void
    {
        $start = Carbon::create(2026, 9, 15, 6, 0, 0);
        $end = Carbon::create(2026, 9, 16, 6, 0, 0);

        $staffPending = User::factory()->create(['role' => 3, 'is_active' => true]);

        LeaveRequest::create([
            'user_id' => $staffPending->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => '2026-09-15',
            'end_date' => '2026-09-15',
            'days_count' => 1.0,
            'status' => LeaveStatus::Pending,
        ]);

        $data = AttendanceAnalyticsService::attendanceRate($start, $end);

        $this->assertSame(0, $data['on_leave']);
        $this->assertSame(1, $data['expected']);
    }

    public function test_staff_on_leave_count_helper(): void
    {
        $refDate = Carbon::create(2026, 9, 15, 10, 0, 0);

        $staff1 = User::factory()->create(['role' => 3, 'is_active' => true]);
        $staff2 = User::factory()->create(['role' => 3, 'is_active' => true]);

        LeaveRequest::create([
            'user_id' => $staff1->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => '2026-09-14',
            'end_date' => '2026-09-16',
            'status' => LeaveStatus::Approved,
        ]);

        LeaveRequest::create([
            'user_id' => $staff2->id,
            'leave_type' => LeaveType::AnnualLeave,
            'start_date' => '2026-09-20',
            'end_date' => '2026-09-21',
            'status' => LeaveStatus::Approved,
        ]);

        $this->assertSame(1, AttendanceAnalyticsService::staffOnLeaveCount($refDate));
    }

    public function test_manager_sees_duty_status_in_staff_list(): void
    {
        $manager = User::factory()->create(['role' => 2]);
        $staff = User::factory()->create(['role' => 3, 'is_active' => true, 'name' => 'Active Worker On MC']);

        LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => LeaveType::MedicalLeave,
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'days_count' => 1.0,
            'status' => LeaveStatus::Approved,
        ]);

        $response = $this->actingAs($manager)->get('/staff/staff');

        $response->assertSuccessful();
        $response->assertSee('Active Worker On MC');
        $response->assertSee('On Leave (Medical Leave (MC))');
    }
}

