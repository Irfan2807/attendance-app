<?php

namespace App\Filament\Staff\Resources\StaffLeaveRequestResource\Pages;

use App\Filament\Staff\Resources\StaffLeaveRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffLeaveRequests extends ListRecords
{
    protected static string $resource = StaffLeaveRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Apply for Leave / MC')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}

