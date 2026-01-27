<?php

namespace App\Filament\Staff\Resources\MileageLogResource\Pages;

use App\Filament\Staff\Resources\MileageLogResource;
use Filament\Resources\Pages\ListRecords;

class ListMileageLogs extends ListRecords
{
    protected static string $resource = MileageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label('Log Mileage'),
        ];
    }
}
