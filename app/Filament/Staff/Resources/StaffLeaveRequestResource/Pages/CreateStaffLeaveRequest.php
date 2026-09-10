<?php

namespace App\Filament\Staff\Resources\StaffLeaveRequestResource\Pages;

use App\Enums\LeaveStatus;
use App\Filament\Staff\Resources\StaffLeaveRequestResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateStaffLeaveRequest extends CreateRecord
{
    protected static string $resource = StaffLeaveRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()?->id;
        $data['status'] = LeaveStatus::Pending;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
