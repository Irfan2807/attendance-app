<?php

namespace App\Filament\Admin\Resources\AttendanceResource\Pages;

use App\Filament\Admin\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('monthly_reports')
                ->label('Monthly Timesheets / Payroll')
                ->icon('heroicon-o-document-chart-bar')
                ->color('warning')
                ->url(route('attendance.monthly.index'))
                ->openUrlInNewTab(),

            Actions\Action::make('export')
                ->label('Export CSV')
                ->url(route('attendance.export'))
                ->openUrlInNewTab(),

            Actions\Action::make('print')
                ->label('Printable')
                ->url(route('attendance.print'))
                ->openUrlInNewTab(),
        ];
    }
}

