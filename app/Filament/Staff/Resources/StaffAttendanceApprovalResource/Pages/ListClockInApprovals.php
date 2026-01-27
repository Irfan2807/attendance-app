<?php

namespace App\Filament\Staff\Resources\StaffAttendanceApprovalResource\Pages;

use App\Filament\Staff\Resources\StaffAttendanceApprovalResource;
use Filament\Resources\Pages\ListRecords;

class ListClockInApprovals extends ListRecords
{
    protected static string $resource = StaffAttendanceApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
