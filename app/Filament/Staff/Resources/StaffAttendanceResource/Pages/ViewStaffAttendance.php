<?php

namespace App\Filament\Staff\Resources\StaffAttendanceResource\Pages;

use App\Filament\Staff\Resources\StaffAttendanceResource;
use Filament\Resources\Pages\ViewRecord;

class ViewStaffAttendance extends ViewRecord
{
    protected static string $resource = StaffAttendanceResource::class;

    public function getTitle(): string
    {
        return 'View Attendance';
    }
}
