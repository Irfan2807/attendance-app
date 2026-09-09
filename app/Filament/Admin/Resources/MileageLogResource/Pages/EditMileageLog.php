<?php

namespace App\Filament\Admin\Resources\MileageLogResource\Pages;

use App\Filament\Admin\Resources\MileageLogResource;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMileageLog extends EditRecord
{
    protected static string $resource = MileageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['vehicle_id']) && isset($data['mileage_reading'])) {
            $vehicle = Vehicle::find($data['vehicle_id']);
            if ($vehicle && $data['mileage_reading'] > $vehicle->current_mileage) {
                $vehicle->update(['current_mileage' => $data['mileage_reading']]);
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

