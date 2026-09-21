<?php

namespace App\Filament\Staff\Resources\StaffAttendanceResource\Pages;

use App\Filament\Staff\Resources\StaffAttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffAttendance extends ListRecords
{
    protected static string $resource = StaffAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('my_monthly_timesheet')
                ->label('My Monthly Timesheet')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->url(fn () => route('attendance.monthly.slip', ['user' => auth()->id()]))
                ->openUrlInNewTab(),
        ];
    }

    public function getTitle(): string
    {
        return 'Attendance Logs';
    }
}
