<?php

namespace App\Filament\Staff\Resources\StaffUserResource\Pages;

use App\Filament\Staff\Resources\StaffUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffUsers extends ListRecords
{
    protected static string $resource = StaffUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
