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
        // Only allow Admin and Manager to open the create page
        if (! Auth::user()?->isManagerOrAdmin()) {
            abort(403);
        }

        parent::mount();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If current user is not admin, force created user's role to Staff
        if (! Auth::user()?->isAdmin()) {
            $data['role'] = \App\Enums\Role::Staff->value;
        }

        return $data;
    }
}
