<?php

namespace App\Filament\Staff\Resources\MileageLogResource\Pages;

use App\Filament\Staff\Resources\MileageLogResource;
use App\Models\Vehicle;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateMileageLog extends CreateRecord
{
    protected static string $resource = MileageLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Automatically set the user_id
        $data['user_id'] = Filament::auth()->id();

        // Update vehicle's current mileage
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

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Mileage logged successfully';
    }
}
