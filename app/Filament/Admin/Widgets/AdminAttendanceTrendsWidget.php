<?php

namespace App\Filament\Admin\Widgets;

use App\Services\AttendanceAnalyticsService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Lazy;

#[Lazy]
class AdminAttendanceTrendsWidget extends ChartWidget
{
    protected static ?string $heading = 'Attendance Trends (Last 30 Operational Days)';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $metrics = Cache::remember('admin_attendance_trend_chart', 120, function (): array {
            $attendanceRate = AttendanceAnalyticsService::attendanceRateTrend(30);
            $lateStarts = AttendanceAnalyticsService::lateStartsTrend(30);
            $overtime = AttendanceAnalyticsService::overtimeTrend(30);
            $turnaround = AttendanceAnalyticsService::approvalTurnaroundTrend(30);

            return [
                'labels' => $attendanceRate['labels'],
                'attendance_rate' => $attendanceRate['rates'],
                'late_starts' => $lateStarts['counts'],
                'overtime_hours' => $overtime['hours'],
                'approval_turnaround' => $turnaround['minutes'],
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Attendance %',
                    'data' => $metrics['attendance_rate'],
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Late Starts',
                    'data' => $metrics['late_starts'],
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Overtime Hours',
                    'data' => $metrics['overtime_hours'],
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Avg Approval Minutes',
                    'data' => $metrics['approval_turnaround'],
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $metrics['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
