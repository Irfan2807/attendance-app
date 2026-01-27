<?php

namespace App\Filament\Staff\Resources\StaffAttendanceApprovalResource\Pages;

use App\Filament\Staff\Resources\StaffAttendanceApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceApproval extends EditRecord
{
    protected static string $resource = StaffAttendanceApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Clock-in ' . (($this->record->status === 'verified') ? 'approved' : 'rejected') . ' successfully!';
    }
}
