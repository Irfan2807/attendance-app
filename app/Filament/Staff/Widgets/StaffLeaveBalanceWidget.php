<?php

namespace App\Filament\Staff\Widgets;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StaffLeaveBalanceWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = Auth::user();
        if (! $user) {
            return [];
        }

        $currentYear = now()->year;

        // 1. Annual Leave
        $alQuota = $user->leaveQuota(LeaveType::AnnualLeave);
        $alTaken = $user->approvedLeaveTaken(LeaveType::AnnualLeave, $currentYear);
        $alRemaining = $user->remainingLeave(LeaveType::AnnualLeave, $currentYear);

        // 2. Medical Leave (MC)
        $mcQuota = $user->leaveQuota(LeaveType::MedicalLeave);
        $mcTaken = $user->approvedLeaveTaken(LeaveType::MedicalLeave, $currentYear);
        $mcRemaining = $user->remainingLeave(LeaveType::MedicalLeave, $currentYear);

        // 3. Hospitalization
        $hospQuota = $user->leaveQuota(LeaveType::Hospitalization);
        $hospTaken = $user->approvedLeaveTaken(LeaveType::Hospitalization, $currentYear);
        $hospRemaining = $user->remainingLeave(LeaveType::Hospitalization, $currentYear);

        // 4. Pending Requests
        $pendingCount = $user->leaveRequests()
            ->where('status', LeaveStatus::Pending->value)
            ->whereYear('start_date', $currentYear)
            ->count();

        return [
            Stat::make('Annual Leave', "{$alRemaining} / {$alQuota} Days")
                ->description("{$alTaken} days used in {$currentYear}")
                ->descriptionIcon('heroicon-m-calendar')
                ->color($alRemaining > 5 ? 'success' : ($alRemaining > 0 ? 'warning' : 'danger'))
                ->icon('heroicon-o-sun'),

            Stat::make('Medical Leave (MC)', "{$mcRemaining} / {$mcQuota} Days")
                ->description("{$mcTaken} days used in {$currentYear}")
                ->descriptionIcon('heroicon-m-document-text')
                ->color($mcRemaining > 5 ? 'success' : ($mcRemaining > 0 ? 'warning' : 'danger'))
                ->icon('heroicon-o-heart'),

            Stat::make('Hospitalization', "{$hospRemaining} / {$hospQuota} Days")
                ->description("{$hospTaken} days used in {$currentYear}")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info')
                ->icon('heroicon-o-building-office-2'),

            Stat::make('Pending Applications', (string) $pendingCount)
                ->description($pendingCount > 0 ? 'Awaiting review' : 'No pending requests')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'gray')
                ->icon('heroicon-o-clock'),
        ];
    }
}
