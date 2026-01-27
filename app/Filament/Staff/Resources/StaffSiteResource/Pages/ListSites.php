<?php

namespace App\Filament\Staff\Resources\StaffSiteResource\Pages;

use App\Filament\Staff\Resources\StaffSiteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSites extends ListRecords
{
    protected static string $resource = StaffSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
