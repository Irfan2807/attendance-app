<?php

namespace App\Filament\Staff\Resources\MileageLogResource\Pages;

use App\Filament\Staff\Resources\MileageLogResource;
use App\Models\Vehicle;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMileageLog extends CreateRecord
{
    protected static string $resource = MileageLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Automatically set the driver to logged in user's primary key ID
        $data['user_id'] = Auth::user()?->id ?? Filament::auth()->user()?->id;

        if (empty($data['site_id'])) {
            $data['site_id'] = null;
        }

        // Harmonize ending mileage and legacy mileage_reading
        $targetMileage = $data['end_mileage'] ?? $data['mileage_reading'] ?? null;
        if ($targetMileage !== null) {
            $data['mileage_reading'] = $targetMileage;
            $data['end_mileage'] = $targetMileage;
        }

        // Calculate distance traveled
        if (isset($data['start_mileage']) && isset($data['end_mileage'])) {
            $data['distance_km'] = max(0, (int) $data['end_mileage'] - (int) $data['start_mileage']);
        }

        // Update vehicle's current mileage if reading is higher
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

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Vehicle trip logged successfully';
    }
}
