<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Shared\Widgets\ManagementAnalyticsStatsWidget;
use App\Services\AttendanceAnalyticsService;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\Lazy;

#[Lazy]
class AdminDataQualityAnalyticsWidget extends ManagementAnalyticsStatsWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $stats = $this->rememberAnalytics('data-quality-analytics', 120, function (): array {
            [$start, $end] = AttendanceAnalyticsService::rollingRange(30);

            return AttendanceAnalyticsService::dataQualityMetrics($start, $end);
        });

        return [
            Stat::make('Missing Coordinates', (string) $stats['missing_coordinates'])
                ->description('Clock-ins with null/zero latitude or longitude (30 days)')
                ->color($stats['missing_coordinates'] > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-map'),

            Stat::make('Auto-Closed Stale Shifts', (string) $stats['auto_closed_stale'])
                ->description('Detected from verification notes (30 days)')
                ->color($stats['auto_closed_stale'] > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-arrow-path-rounded-square'),

            Stat::make('Repeat Temporary Users', (string) $stats['repeated_temporary_users'])
                ->description('Users with at least 2 temporary statuses (30 days)')
                ->color($stats['repeated_temporary_users'] > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-user-minus'),

            Stat::make('Repeat Offender', $stats['repeat_offender_name'])
                ->description($stats['repeat_offender_count'].' infractions in last 30 days')
                ->color($stats['repeat_offender_count'] > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-shield-exclamation'),
        ];
    }
}
