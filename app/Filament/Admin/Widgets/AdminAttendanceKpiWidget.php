<?php

namespace App\Filament\Admin\Widgets;

use App\Services\AttendanceAnalyticsService;
use App\Services\AttendanceMetricsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Lazy;

#[Lazy]
class AdminAttendanceKpiWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $metrics = Cache::remember('admin_attendance_kpi_stats', 120, function (): array {
            [$todayStart, $todayEnd] = AttendanceAnalyticsService::operationalDayRange();
            [$monthStart, $monthEnd] = AttendanceAnalyticsService::rollingRange(30);

            $todayRate = AttendanceAnalyticsService::attendanceRate($todayStart, $todayEnd);
            $todayRateTrend = AttendanceAnalyticsService::attendanceRateTrend(7);
            $pending = AttendanceAnalyticsService::pendingApprovalMetrics();
            $monthOvertimeMinutes = AttendanceAnalyticsService::overtimeMinutes($monthStart, $monthEnd);
            $monthOvertimeTrend = AttendanceAnalyticsService::overtimeTrend(30);
            $siteCoverage = AttendanceAnalyticsService::siteCoverage($monthStart, $monthEnd);
            $infractions = AttendanceAnalyticsService::infractionCount($monthStart, $monthEnd);
            $quality = AttendanceAnalyticsService::dataQualityMetrics($monthStart, $monthEnd);

            return [
                'today_rate' => $todayRate,
                'today_rate_trend' => $todayRateTrend,
                'pending' => $pending,
                'month_overtime_minutes' => $monthOvertimeMinutes,
                'month_overtime_trend' => $monthOvertimeTrend,
                'site_coverage' => $siteCoverage,
                'infractions' => $infractions,
                'quality' => $quality,
            ];
        });

        return [
            Stat::make('Attendance Rate (Operational Day)', $metrics['today_rate']['rate'].'%')
                ->description($metrics['today_rate']['present'].' of '.$metrics['today_rate']['total'].' staff')
                ->color($metrics['today_rate']['rate'] >= 80 ? 'success' : ($metrics['today_rate']['rate'] >= 60 ? 'warning' : 'danger'))
                ->icon('heroicon-o-users')
                ->chart($metrics['today_rate_trend']['rates']),

            Stat::make('Pending Approvals', (string) $metrics['pending']['total'])
                ->description($metrics['pending']['older_than_24h'].' older than 24h')
                ->color($metrics['pending']['older_than_24h'] > 0 ? 'danger' : 'warning')
                ->icon('heroicon-o-clock'),

            Stat::make('Overtime (30 Days)', AttendanceMetricsService::formatMinutes($metrics['month_overtime_minutes']))
                ->description('Rolling 30-day trend')
                ->color('info')
                ->icon('heroicon-o-calendar-days')
                ->chart($metrics['month_overtime_trend']['hours']),

            Stat::make('Site Coverage', $metrics['site_coverage']['coverage_rate'].'%')
                ->description($metrics['site_coverage']['active_sites'].'/'.$metrics['site_coverage']['total_active_sites'].' active sites')
                ->color($metrics['site_coverage']['coverage_rate'] >= 70 ? 'success' : 'warning')
                ->icon('heroicon-o-map-pin'),

            Stat::make('Infractions (30 Days)', (string) $metrics['infractions'])
                ->description('Top offender: '.$metrics['quality']['repeat_offender_name'])
                ->color($metrics['infractions'] > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
