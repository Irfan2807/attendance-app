<?php

namespace App\Filament\Staff\Resources\StaffLeaveApprovalResource\Pages;

use App\Filament\Staff\Resources\StaffLeaveApprovalResource;
use Filament\Resources\Pages\ListRecords;

class ListStaffLeaveApprovals extends ListRecords
{
    protected static string $resource = StaffLeaveApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

