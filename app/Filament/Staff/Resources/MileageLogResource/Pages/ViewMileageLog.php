<?php

namespace App\Filament\Staff\Resources\MileageLogResource\Pages;

use App\Filament\Staff\Resources\MileageLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMileageLog extends ViewRecord
{
    protected static string $resource = MileageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => MileageLogResource::canEdit($this->record)),
        ];
    }
}
