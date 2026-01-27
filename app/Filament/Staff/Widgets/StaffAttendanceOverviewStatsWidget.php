<?php

namespace App\Filament\Staff\Widgets;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Lazy;

#[Lazy]
class StaffAttendanceOverviewStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Cache stats for 5 minutes to avoid expensive calculations
        return Cache::remember('staff_stats_' . auth()->id(), 300, function () {
            $today = Carbon::today();
            $thisMonth = Carbon::now()->startOfMonth();

            // Total staff (excluding managers)
            $totalStaff = User::where('role', 3)->count();

            // Staff who clocked in today
            $staffTodayCount = Attendance::where('user_id', '!=', auth()->id())
                ->whereDate('clock_in_time', $today)
                ->distinct('user_id')
                ->count();

            // Attendance rate today
            $attendanceRateToday = $totalStaff > 0 ? round(($staffTodayCount / $totalStaff) * 100) : 0;

            // Team total hours this month - use SELECT SUM instead of fetching all records
            $teamTotalHours = Attendance::where('user_id', '!=', auth()->id())
                ->whereBetween('clock_in_time', [$thisMonth, Carbon::now()])
                ->whereNotNull('clock_out_time')
                ->get(['clock_in_time', 'clock_out_time'])
                ->reduce(function ($carry, $record) {
                    return $carry + ($record->clock_in_time->diffInMinutes($record->clock_out_time) / 60);
                }, 0);

            // Pending approvals
            $pendingCount = Attendance::whereIn('status', ['pending', 'temporary'])
                ->where('user_id', '!=', auth()->id())
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
