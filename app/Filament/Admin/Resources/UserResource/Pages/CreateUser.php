<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function mount(): void
    {
        // Only allow Admin (1) and Manager (2) to open the create page
        if (! Auth::user() || ! in_array(Auth::user()->role, [1, 2])) {
            abort(403);
        }

        parent::mount();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If current user is not admin, force created user's role to Staff (3)
        if (! Auth::user() || Auth::user()->role !== 1) {
            $data['role'] = 3;
        }

        return $data;
    }
}
