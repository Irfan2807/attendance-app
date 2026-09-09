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
        if (! Auth::user()?->isManagerOrAdmin()) {
            abort(403);
        }

        parent::mount();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Force created user to be Staff regardless of input
        $data['role'] = \App\Enums\Role::Staff->value;

        return $data;
    }
}
