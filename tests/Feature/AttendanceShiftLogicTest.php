<?php

namespace Tests\Feature;

use App\Filament\Staff\Widgets\StaffAttendanceOverviewStatsWidget;
use App\Models\Attendance;
use App\Models\User;
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
}
