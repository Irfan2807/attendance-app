<?php

namespace App\Filament\Staff\Resources\StaffUserResource\Pages;

use App\Filament\Staff\Resources\StaffUserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateStaffUser extends CreateRecord
{
    protected static string $resource = StaffUserResource::class;

    public function mount(): void
    {
        if (! Auth::user() || ! in_array(Auth::user()->role, [1, 2])) {
            abort(403);
        }

        parent::mount();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Force created user to be Staff (3) regardless of input
        $data['role'] = 3;

        return $data;
    }
}
