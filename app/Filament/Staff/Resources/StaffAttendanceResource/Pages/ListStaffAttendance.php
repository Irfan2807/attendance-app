<?php

namespace App\Filament\Staff\Resources\StaffAttendanceResource\Pages;

use App\Filament\Staff\Resources\StaffAttendanceResource;
use Filament\Resources\Pages\ListRecords;

class ListStaffAttendance extends ListRecords
{
    protected static string $resource = StaffAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    public function getTitle(): string
    {
        return 'Attendance Logs';
    }
}
