<?php

namespace App\Filament\Admin\Resources\MileageLogResource\Pages;

use App\Filament\Admin\Resources\MileageLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMileageLogs extends ListRecords
{
    protected static string $resource = MileageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

