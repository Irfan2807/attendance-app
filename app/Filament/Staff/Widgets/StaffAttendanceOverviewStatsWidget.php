<?php

namespace App\Filament\Staff\Widgets;

use App\Models\Attendance;
use App\Models\User;
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
    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->role === 2;
    }

    protected function getStats(): array
    {
        // Cache stats for 2 minutes to reduce repeated dashboard load while keeping fresh counts.
        return Cache::remember('staff_stats_' . Auth::id(), 120, function () {
            $dbDriver = DB::connection()->getDriverName();
            [$todayStart, $todayEnd] = AttendanceWindowService::operationalDayRange();
            $thisMonth = Carbon::now()->startOfMonth();

            // Total staff (excluding managers)
            $totalStaff = User::where('role', 3)->count();

            // Staff who clocked in today
            $staffTodayCount = Attendance::where('user_id', '!=', Auth::id())
                ->whereBetween('clock_in_time', [$todayStart, $todayEnd])
                ->select('user_id')
                ->distinct()
                ->count('user_id');

            // Attendance rate today
            $attendanceRateToday = $totalStaff > 0 ? round(($staffTodayCount / $totalStaff) * 100) : 0;

            // Team total hours this month, fully aggregated in SQL.
            $teamTotalMinutesQuery = Attendance::where('user_id', '!=', Auth::id())
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

            $teamTotalHours = $teamTotalMinutes / 60;

            // Pending approvals
            $pendingCount = Attendance::whereIn('status', ['pending', 'temporary'])
                ->where('user_id', '!=', Auth::id())
                ->count();

            return [
                Stat::make('Attendance Rate (Today)', $attendanceRateToday . '%')
                    ->description($staffTodayCount . ' of ' . $totalStaff . ' staff')
                    ->color($attendanceRateToday >= 80 ? 'success' : ($attendanceRateToday >= 60 ? 'warning' : 'danger'))
                    ->icon('heroicon-o-users'),

                Stat::make('Team Total Hours', number_format($teamTotalHours, 1) . 'h')
                    ->description('This month')
                    ->color('info')
                    ->icon('heroicon-o-clock'),

                Stat::make('Pending Approvals', $pendingCount)
                    ->description('Awaiting review')
                    ->color($pendingCount > 0 ? 'warning' : 'success')
                    ->icon('heroicon-o-exclamation-circle'),
            ];
        });
    }
}
