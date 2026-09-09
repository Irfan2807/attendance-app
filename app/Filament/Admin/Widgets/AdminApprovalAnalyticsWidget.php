<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Shared\Widgets\ManagementAnalyticsStatsWidget;
use App\Services\AttendanceAnalyticsService;
use App\Services\AttendanceMetricsService;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\Lazy;

#[Lazy]
class AdminApprovalAnalyticsWidget extends ManagementAnalyticsStatsWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $stats = $this->rememberAnalytics('approval-analytics', 120, function (): array {
            [$start, $end] = AttendanceAnalyticsService::rollingRange(30);
            $approval = AttendanceAnalyticsService::approvalAnalytics($start, $end);
            $pending = AttendanceAnalyticsService::pendingApprovalMetrics();

            return [
                'approval' => $approval,
                'pending' => $pending,
            ];
        });

        $hasDecisions = ($stats['approval']['decision_count'] ?? 0) > 0;

        return [
            Stat::make(
                'Avg Time to Decision',
                $hasDecisions
                    ? AttendanceMetricsService::formatMinutes((int) $stats['approval']['avg_turnaround_minutes'])
                    : 'N/A'
            )
                ->description('Based on approved/rejected records in last 30 days')
                ->color($hasDecisions ? ($stats['approval']['avg_turnaround_minutes'] > 720 ? 'danger' : 'success') : 'gray')
                ->icon('heroicon-o-bolt'),

            Stat::make(
                'Approval Rate',
                $hasDecisions ? $stats['approval']['approval_rate'].'%' : 'N/A'
            )
                ->description($stats['approval']['approved_count'].' approvals / '.$stats['approval']['decision_count'].' decisions')
                ->color($hasDecisions ? ($stats['approval']['approval_rate'] >= 80 ? 'success' : 'warning') : 'gray')
                ->icon('heroicon-o-check-badge'),

            Stat::make(
                'Rejection Rate',
                $hasDecisions ? $stats['approval']['rejection_rate'].'%' : 'N/A'
            )
                ->description($stats['approval']['rejected_count'].' rejected')
                ->color($hasDecisions ? ($stats['approval']['rejection_rate'] > 20 ? 'danger' : 'gray') : 'gray')
                ->icon('heroicon-o-x-circle'),

            Stat::make('Pending Age Buckets', '>24h: '.$stats['pending']['older_than_24h'])
                ->description('>48h: '.$stats['pending']['older_than_48h'])
                ->color($stats['pending']['older_than_48h'] > 0 ? 'danger' : 'warning')
                ->icon('heroicon-o-exclamation-circle'),

            Stat::make('Top Approver', $stats['approval']['top_manager_summary'])
                ->description('Highest decision volume in last 30 days')
                ->color('info')
                ->icon('heroicon-o-user-group'),
        ];
    }
}
