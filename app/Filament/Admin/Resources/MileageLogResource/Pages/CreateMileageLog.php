<?php

namespace App\Filament\Admin\Resources\MileageLogResource\Pages;

use App\Filament\Admin\Resources\MileageLogResource;
use App\Models\Vehicle;
use Filament\Resources\Pages\CreateRecord;

class CreateMileageLog extends CreateRecord
{
    protected static string $resource = MileageLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $targetMileage = $data['end_mileage'] ?? $data['mileage_reading'] ?? null;
        if ($targetMileage !== null) {
            $data['mileage_reading'] = $targetMileage;
            $data['end_mileage'] = $targetMileage;
        }

        if (empty($data['user_id'])) {
            $data['user_id'] = \Illuminate\Support\Facades\Auth::user()?->id ?? \Filament\Facades\Filament::auth()->user()?->id;
        }

        if (empty($data['site_id'])) {
            $data['site_id'] = null;
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
