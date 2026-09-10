<?php

namespace App\Filament\Staff\Widgets;

use App\Enums\Role;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AttendanceAnalyticsService;
use App\Services\AttendanceMetricsService;
use App\Services\AttendanceWindowService;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Lazy;

#[Lazy]
class StaffAttendanceOverviewStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->isManagerOrAdmin();
    }

    protected function getStats(): array
    {
        // Cache stats for 2 minutes to reduce repeated dashboard load while keeping fresh counts.
        return Cache::remember('staff_stats_v4_' . (Auth::user()?->id ?? 0), 120, function () {
            $dbDriver = DB::connection()->getDriverName();
            [$todayStart, $todayEnd] = AttendanceWindowService::operationalDayRange();
            $thisMonth = Carbon::now()->startOfMonth();

            // Total active staff (excluding managers)
            $totalStaff = User::where('role', Role::Staff->value)->where('is_active', true)->count();

            // Staff who clocked in today
            $staffTodayCount = Attendance::whereHas('user', fn ($q) => $q->where('role', Role::Staff->value))
                ->where('status', '!=', 'rejected')
                ->whereBetween('clock_in_time', [$todayStart, $todayEnd])
                ->select('user_id')
                ->distinct()
                ->count('user_id');

            // Staff on approved leave today
            $onLeaveTodayCount = AttendanceAnalyticsService::staffOnLeaveCount($todayStart);

            // Attendance rate today
            $attendanceRateToday = $totalStaff > 0 ? round(($staffTodayCount / $totalStaff) * 100) : 0;

            // Team total hours this month, fully aggregated in SQL.
            $teamTotalMinutesQuery = Attendance::whereHas('user', fn ($q) => $q->where('role', Role::Staff->value))
                ->where('status', '!=', 'rejected')
                ->whereBetween('clock_in_time', [$thisMonth, Carbon::now()])
                ->whereNotNull('clock_out_time');

            if ($dbDriver === 'sqlite') {
                $teamTotalMinutes = (int) $teamTotalMinutesQuery
                    ->get(['clock_in_time', 'clock_out_time'])
                    ->sum(function (Attendance $attendance): int {
                        if (!$attendance->clock_in_time || !$attendance->clock_out_time) {
                            return 0;
                        }

                        return $attendance->clock_in_time->diffInMinutes($attendance->clock_out_time);
                    });
            } else {
                $teamTotalMinutes = (int) $teamTotalMinutesQuery
                    ->selectRaw('COALESCE(SUM(TIMESTAMPDIFF(MINUTE, clock_in_time, clock_out_time)), 0) as total_minutes')
                    ->value('total_minutes');
            }

            $formattedTeamHours = AttendanceMetricsService::formatHoursAndMinutes($teamTotalMinutes);

            // Site coverage for today
            $coverage = AttendanceAnalyticsService::siteCoverage($todayStart, $todayEnd);

            // Pending shift check-ins + pending leave requests
            $currentUserId = Auth::user()?->id;
            $isAdminViewer = Auth::user()?->isAdmin() ?? false;

            $pendingAttendanceCount = Attendance::whereIn('status', ['pending', 'temporary'])
                ->when(! $isAdminViewer, fn ($q) => $q->whereHas('user', fn ($uq) => $uq->where('role', Role::Staff->value)))
                ->where('user_id', '!=', $currentUserId)
                ->count();

            $pendingLeaveCount = \App\Models\LeaveRequest::where('status', \App\Enums\LeaveStatus::Pending->value)
                ->where('user_id', '!=', $currentUserId)
                ->when(! $isAdminViewer, fn ($q) => $q->whereHas('user', fn ($uq) => $uq->where('role', Role::Staff->value)))
                ->count();

            $totalPendingCount = $pendingAttendanceCount + $pendingLeaveCount;

            $rateDescription = $onLeaveTodayCount > 0
                ? "{$staffTodayCount} present · {$onLeaveTodayCount} on leave ({$totalStaff} active)"
                : "{$staffTodayCount} of {$totalStaff} staff";

            $pendingDescription = $pendingLeaveCount > 0
                ? "{$pendingAttendanceCount} shifts · {$pendingLeaveCount} leaves"
                : ($totalPendingCount > 0 ? 'Awaiting review' : 'All caught up');

            return [
                Stat::make('Attendance Rate (Today)', $attendanceRateToday . '%')
                    ->description($rateDescription)
                    ->color($attendanceRateToday >= 80 ? 'success' : ($attendanceRateToday >= 60 ? 'warning' : 'danger'))
                    ->icon('heroicon-o-users'),

                Stat::make(
                    'Site Coverage',
                    $coverage['has_configured_sites'] ? $coverage['coverage_rate'] . '%' : 'N/A'
                )
                    ->description(
                        $coverage['has_configured_sites']
                            ? $coverage['active_sites'] . '/' . $coverage['total_active_sites'] . ' active sites manned'
                            : 'No active sites configured'
                    )
                    ->color($coverage['has_configured_sites'] ? ($coverage['coverage_rate'] >= 70 ? 'success' : 'warning') : 'gray')
                    ->icon('heroicon-o-map-pin'),

                Stat::make('Team Total Hours', $formattedTeamHours)
                    ->description('This month')
                    ->color('info')
                    ->icon('heroicon-o-clock'),

                Stat::make('Pending Approvals', $totalPendingCount)
                    ->description($pendingDescription)
                    ->color($totalPendingCount > 0 ? 'warning' : 'success')
                    ->icon('heroicon-o-exclamation-circle'),
            ];
        });
    }
}
