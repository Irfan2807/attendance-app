<?php

namespace App\Filament\Staff\Resources\VehicleResource\Pages;

use App\Filament\Staff\Resources\VehicleResource;
use Filament\Resources\Pages\ListRecords;

class ListVehicles extends ListRecords
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->visible(fn() => \Illuminate\Support\Facades\Auth::user()->role === 2),
        ];
    }
}
