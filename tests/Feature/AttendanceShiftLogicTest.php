<?php

namespace Tests\Feature;

use App\Filament\Staff\Widgets\StaffAttendanceOverviewStatsWidget;
use App\Models\Attendance;
use App\Models\Site;
use App\Models\User;
use App\Services\AttendanceAnalyticsService;
use App\Services\AttendanceMetricsService;
use App\Services\AttendanceWindowService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceShiftLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_day_shift_window_by_default(): void
    {
        config()->set('attendance.day_shift_starts_at', 8);

        $reference = Carbon::create(2026, 4, 15, 10, 0, 0);
        $start = AttendanceWindowService::operationalDayStart($reference, 'day');
        $end = AttendanceWindowService::operationalDayEnd($reference, 'day');

        $this->assertSame('2026-04-15 08:00:00', $start->format('Y-m-d H:i:s'));
        $this->assertSame('2026-04-16 08:00:00', $end->format('Y-m-d H:i:s'));
    }

    public function test_it_uses_night_shift_window_when_selected(): void
    {
        config()->set('attendance.night_shift_starts_at', 17);

        $reference = Carbon::create(2026, 4, 15, 22, 0, 0);
        $start = AttendanceWindowService::operationalDayStart($reference, 'night');
        $end = AttendanceWindowService::operationalDayEnd($reference, 'night');

        $this->assertSame('2026-04-15 17:00:00', $start->format('Y-m-d H:i:s'));
        $this->assertSame('2026-04-16 17:00:00', $end->format('Y-m-d H:i:s'));
    }

    public function test_it_detects_stale_open_shifts_after_max_hours(): void
    {
        config()->set('attendance.max_shift_hours', 16);

        $clockInTime = Carbon::create(2026, 4, 15, 6, 0, 0);
        $reference = Carbon::create(2026, 4, 15, 23, 0, 0);

        $this->assertTrue(AttendanceWindowService::isStaleShift($clockInTime, $reference));
    }

    public function test_manager_widget_is_hidden_from_staff(): void
    {
        /** @var User $staff */
        $staff = User::factory()->create(['role' => 3]);
        /** @var User $manager */
        $manager = User::factory()->create(['role' => 2]);

        $this->actingAs($staff);
        $this->assertFalse(StaffAttendanceOverviewStatsWidget::canView());

        $this->actingAs($manager);
        $this->assertTrue(StaffAttendanceOverviewStatsWidget::canView());
    }

    public function test_overtime_starts_after_standard_workday_hours(): void
    {
        config()->set('attendance.standard_workday_hours', 8);

        $attendance = new Attendance([
            'clock_in_time' => Carbon::create(2026, 4, 15, 8, 0, 0),
            'clock_out_time' => Carbon::create(2026, 4, 15, 18, 30, 0),
        ]);

        $this->assertSame(150, AttendanceMetricsService::overtimeMinutes($attendance));
    }

    public function test_night_shift_overtime_starts_after_standard_hours(): void
    {
        config()->set('attendance.night_shift_standard_hours', 8);

        $attendance = new Attendance([
            'clock_in_time' => Carbon::create(2026, 4, 15, 17, 0, 0),
            'clock_out_time' => Carbon::create(2026, 4, 16, 3, 30, 0),
        ]);

        $this->assertSame(150, AttendanceMetricsService::overtimeMinutes($attendance));
    }

    public function test_overtime_is_zero_when_work_is_shorter_than_standard_hours(): void
    {
        config()->set('attendance.standard_workday_hours', 8);

        $attendance = new Attendance([
            'clock_in_time' => Carbon::create(2026, 4, 15, 8, 0, 0),
            'clock_out_time' => Carbon::create(2026, 4, 15, 15, 45, 0),
        ]);

        $this->assertSame(0, AttendanceMetricsService::overtimeMinutes($attendance));
    }

    public function test_site_coverage_only_counts_registered_active_sites_and_clamps_to_100(): void
    {
        Site::create(['name' => 'HQ Site', 'latitude' => 3.0, 'longitude' => 101.0, 'radius_meters' => 100, 'is_active' => true]);
        Site::create(['name' => 'Warehouse Site', 'latitude' => 3.1, 'longitude' => 101.1, 'radius_meters' => 100, 'is_active' => true]);
        Site::create(['name' => 'Old Inactive Site', 'latitude' => 3.2, 'longitude' => 101.2, 'radius_meters' => 100, 'is_active' => false]);

        $user = User::factory()->create(['role' => 3]);
        $start = Carbon::create(2026, 4, 1, 0, 0, 0);
        $end = Carbon::create(2026, 4, 30, 23, 59, 59);

        // Attendance at HQ Site
        Attendance::create([
            'user_id' => $user->id,
            'site_name' => 'HQ Site',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::create(2026, 4, 15, 8, 0, 0),
        ]);

        // Attendance at unregistered / unknown site name
        Attendance::create([
            'user_id' => $user->id,
            'site_name' => 'Unknown Location / Remote',
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::create(2026, 4, 16, 8, 0, 0),
        ]);

        $coverage = AttendanceAnalyticsService::siteCoverage($start, $end);

        $this->assertSame(1, $coverage['active_sites']);
        $this->assertSame(2, $coverage['total_active_sites']);
        $this->assertSame(50.0, $coverage['coverage_rate']);
    }

    public function test_late_starts_considers_day_and_night_shift_windows(): void
    {
        config()->set('attendance.late_grace_minutes', 15);
        config()->set('attendance.day_shift_starts_at', 8);
        config()->set('attendance.night_shift_starts_at', 17);

        $user = User::factory()->create(['role' => 3]);
        $ref = Carbon::create(2026, 4, 15, 20, 0, 0);

        // On-time day shift (08:10 <= 08:15)
        Attendance::create([
            'user_id' => $user->id,
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::create(2026, 4, 15, 8, 10, 0),
        ]);

        // Late night shift (17:25 > 17:15)
        Attendance::create([
            'user_id' => $user->id,
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::create(2026, 4, 15, 17, 25, 0),
        ]);

        // On-time night shift (17:10 <= 17:15)
        Attendance::create([
            'user_id' => $user->id,
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::create(2026, 4, 15, 17, 10, 0),
        ]);

        $trend = AttendanceAnalyticsService::lateStartsTrend(1, $ref);
        $this->assertSame([1], $trend['counts']);
    }

    public function test_attendance_rate_excludes_rejected_records(): void
    {
        $user1 = User::factory()->create(['role' => 3]);
        $user2 = User::factory()->create(['role' => 3]);

        $start = Carbon::create(2026, 4, 15, 8, 0, 0);
        $end = Carbon::create(2026, 4, 16, 8, 0, 0);

        // user1 has rejected attendance
        Attendance::create([
            'user_id' => $user1->id,
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'rejected',
            'clock_in_time' => Carbon::create(2026, 4, 15, 9, 0, 0),
        ]);

        // user2 has approved attendance
        Attendance::create([
            'user_id' => $user2->id,
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::create(2026, 4, 15, 9, 0, 0),
        ]);

        $rateData = AttendanceAnalyticsService::attendanceRate($start, $end);
        $this->assertSame(1, $rateData['present']);
        $this->assertSame(2, $rateData['total']);
        $this->assertSame(50.0, $rateData['rate']);
    }

    public function test_early_bird_clock_in_is_included_in_today_operational_day(): void
    {
        config()->set('attendance.day_shift_starts_at', 8);
        config()->set('attendance.early_arrival_buffer_hours', 2);

        $earlyRef = Carbon::create(2026, 4, 15, 7, 30, 0);
        $start = AttendanceWindowService::operationalDayStart($earlyRef, 'day');
        [$rangeStart, $rangeEnd] = AttendanceWindowService::operationalDayRange($earlyRef, 'day');

        $this->assertSame('2026-04-15 08:00:00', $start->format('Y-m-d H:i:s'));
        $this->assertSame('2026-04-15 06:00:00', $rangeStart->format('Y-m-d H:i:s'));
        $this->assertSame('2026-04-16 06:00:00', $rangeEnd->format('Y-m-d H:i:s'));

        $user = User::factory()->create(['role' => 3, 'is_active' => true]);
        Attendance::create([
            'user_id' => $user->id,
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::create(2026, 4, 15, 7, 30, 0),
        ]);

        $rateData = AttendanceAnalyticsService::attendanceRate($rangeStart, $rangeEnd);
        $this->assertSame(1, $rateData['present']);
        $this->assertSame(1, $rateData['total']);
        $this->assertSame(100.0, $rateData['rate']);
    }

    public function test_inactive_staff_are_excluded_from_attendance_rate_denominator(): void
    {
        $activeStaff = User::factory()->create(['role' => 3, 'is_active' => true]);
        $inactiveStaff = User::factory()->create(['role' => 3, 'is_active' => false]);

        $start = Carbon::create(2026, 4, 15, 6, 0, 0);
        $end = Carbon::create(2026, 4, 16, 6, 0, 0);

        Attendance::create([
            'user_id' => $activeStaff->id,
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::create(2026, 4, 15, 8, 30, 0),
        ]);

        $rateData = AttendanceAnalyticsService::attendanceRate($start, $end);
        $this->assertSame(1, $rateData['present']);
        $this->assertSame(1, $rateData['total']);
        $this->assertSame(100.0, $rateData['rate']);
    }

    public function test_approval_turnaround_is_calculated_from_clock_out_time(): void
    {
        $manager = User::factory()->create(['role' => 2]);
        $staff = User::factory()->create(['role' => 3, 'is_active' => true]);

        $start = Carbon::create(2026, 4, 1, 0, 0, 0);
        $end = Carbon::create(2026, 4, 30, 23, 59, 59);

        // Staff worked 8:00 to 17:00 (9 hours), manager approved at 17:30 (30 minutes after clock-out)
        Attendance::create([
            'user_id' => $staff->id,
            'latitude' => 3.0,
            'longitude' => 101.0,
            'status' => 'approved',
            'clock_in_time' => Carbon::create(2026, 4, 15, 8, 0, 0),
            'clock_out_time' => Carbon::create(2026, 4, 15, 17, 0, 0),
            'approved_at' => Carbon::create(2026, 4, 15, 17, 30, 0),
            'approved_by' => $manager->id,
        ]);

        $analytics = AttendanceAnalyticsService::approvalAnalytics($start, $end);

        $this->assertSame(1, $analytics['approved_count']);
        $this->assertSame(30.0, $analytics['avg_turnaround_minutes']);
    }

    public function test_site_coverage_handles_zero_registered_sites_gracefully(): void
    {
        Site::query()->delete();

        $start = Carbon::create(2026, 4, 1, 0, 0, 0);
        $end = Carbon::create(2026, 4, 30, 23, 59, 59);

        $coverage = AttendanceAnalyticsService::siteCoverage($start, $end);

        $this->assertFalse($coverage['has_configured_sites']);
        $this->assertSame(0, $coverage['total_active_sites']);
        $this->assertSame(0, $coverage['active_sites']);
        $this->assertSame(0.0, $coverage['coverage_rate']);
    }
}
