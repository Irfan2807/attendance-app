<?php

namespace App\Filament\Staff\Widgets;

use App\Models\AttendanceInfraction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;

#[Lazy]
class AttendanceWarningsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = [
        'default' => 1,
        'lg' => 2,
    ];

    protected function getStats(): array
    {
        $user = Auth::user();

        // Current month warnings
        $monthlyWarnings = AttendanceInfraction::getMonthlyWarningCount($user->id);
        $needsEscalation = AttendanceInfraction::needsManagerEscalation($user->id);
        $totalInfractions = $user->incomplete_clock_out_count;

        return [
            Stat::make('This Month Warnings', $monthlyWarnings)
                ->description($monthlyWarnings === 0 ? '✓ No warnings' : ($monthlyWarnings >= 3 ? '🚨 Manager review needed!' : "({$monthlyWarnings}/3 warnings)"))
                ->color($monthlyWarnings === 0 ? 'success' : ($monthlyWarnings >= 3 ? 'danger' : 'warning'))
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('Total Infractions', $totalInfractions)
                ->description('All-time incomplete clock-outs')
                ->color($totalInfractions === 0 ? 'success' : 'warning')
                ->icon('heroicon-o-chart-bar'),

            Stat::make('Status', $needsEscalation ? '⚠️ Escalated' : '✓ Good Standing')
                ->description($needsEscalation ? 'Manager approval needed' : 'You\'re all caught up')
                ->color($needsEscalation ? 'danger' : 'success')
                ->icon($needsEscalation ? 'heroicon-o-shield-exclamation' : 'heroicon-o-check-circle'),
        ];
    }
}
