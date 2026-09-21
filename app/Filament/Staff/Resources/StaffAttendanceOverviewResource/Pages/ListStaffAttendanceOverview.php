<?php

namespace App\Filament\Staff\Resources\StaffAttendanceOverviewResource\Pages;

use App\Filament\Staff\Resources\StaffAttendanceOverviewResource;
use App\Filament\Staff\Widgets\StaffAttendanceOverviewStatsWidget;
use App\Filament\Staff\Widgets\StaffAttendanceTrendsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffAttendanceOverview extends ListRecords
{
    protected static string $resource = StaffAttendanceOverviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('monthly_reports')
                ->label('Monthly Timesheets / Payroll')
                ->icon('heroicon-o-document-chart-bar')
                ->color('warning')
                ->url(route('attendance.monthly.index'))
                ->openUrlInNewTab(),

            Actions\Action::make('export_csv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(route('attendance.export'))
                ->openUrlInNewTab(),

            Actions\Action::make('print_view')
                ->label('Printable')
                ->icon('heroicon-o-printer')
                ->url(route('attendance.print'))
                ->openUrlInNewTab(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StaffAttendanceOverviewStatsWidget::class,
            StaffAttendanceTrendsWidget::class,
        ];
    }
}
