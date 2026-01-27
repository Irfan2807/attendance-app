<?php

namespace App\Filament\Staff\Resources\StaffAttendanceOverviewResource\Pages;

use App\Filament\Staff\Resources\StaffAttendanceOverviewResource;
use Filament\Resources\Pages\EditRecord;

class EditStaffAttendanceOverview extends EditRecord
{
    protected static string $resource = StaffAttendanceOverviewResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
