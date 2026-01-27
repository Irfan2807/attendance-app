<?php

namespace App\Filament\Staff\Resources\VehicleResource\Pages;

use App\Filament\Staff\Resources\VehicleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVehicle extends ViewRecord
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => \Illuminate\Support\Facades\Auth::user()->role === 2),
        ];
    }
}
