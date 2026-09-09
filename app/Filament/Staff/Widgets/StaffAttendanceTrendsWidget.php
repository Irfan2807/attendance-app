<?php

namespace App\Filament\Staff\Widgets;

use App\Services\AttendanceAnalyticsService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Lazy;

#[Lazy]
class StaffAttendanceTrendsWidget extends ChartWidget
{
    protected static ?string $heading = 'Team Attendance & Punctuality Trends';

    protected static ?string $description = 'Tracks daily workforce attendance rate, on-time arrivals, and overtime hours.';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = '14';

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->isManagerOrAdmin();
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 Days (Weekly)',
            '14' => 'Last 14 Days (Bi-weekly)',
            '30' => 'Last 30 Days (Monthly)',
        ];
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?? 14);

        $metrics = Cache::remember('staff_attendance_trend_chart_v2_' . $days, 120, function () use ($days): array {
            $attendance = AttendanceAnalyticsService::attendanceRateTrend($days);
            $punctuality = AttendanceAnalyticsService::punctualityRateTrend($days);
            $overtime = AttendanceAnalyticsService::overtimeTrend($days);

            return [
                'labels' => $attendance['labels'],
                'attendance_rate' => $attendance['rates'],
                'punctuality_rate' => $punctuality['rates'],
                'overtime_hours' => $overtime['hours'],
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Attendance Rate (%)',
                    'data' => $metrics['attendance_rate'],
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'tension' => 0.3,
                    'yAxisID' => 'y',
                    'fill' => true,
                ],
                [
                    'label' => 'Punctuality Rate (%)',
                    'data' => $metrics['punctuality_rate'],
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'tension' => 0.3,
                    'yAxisID' => 'y',
                    'fill' => true,
                ],
                [
                    'label' => 'Overtime (Hours)',
                    'data' => $metrics['overtime_hours'],
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                    'tension' => 0.3,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $metrics['labels'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'min' => 0,
                    'max' => 100,
                    'title' => [
                        'display' => true,
                        'text' => 'Rate (%)',
                    ],
                    'ticks' => [
                        'stepSize' => 20,
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'min' => 0,
                    'title' => [
                        'display' => true,
                        'text' => 'Overtime (Hours)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
