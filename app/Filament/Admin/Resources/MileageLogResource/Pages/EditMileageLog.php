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
        $targetMileage = $data['end_mileage'] ?? $data['mileage_reading'] ?? null;
        if ($targetMileage !== null) {
            $data['mileage_reading'] = $targetMileage;
            $data['end_mileage'] = $targetMileage;
        }

        if (isset($data['start_mileage']) && isset($data['end_mileage'])) {
            $data['distance_km'] = max(0, (int) $data['end_mileage'] - (int) $data['start_mileage']);
        }

        if (isset($data['vehicle_id']) && $targetMileage !== null) {
            $vehicle = Vehicle::find($data['vehicle_id']);
            if ($vehicle && $targetMileage > $vehicle->current_mileage) {
                $vehicle->update(['current_mileage' => $targetMileage]);
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
