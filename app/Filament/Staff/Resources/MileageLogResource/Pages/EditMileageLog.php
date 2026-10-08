<?php

namespace App\Filament\Staff\Resources\MileageLogResource\Pages;

use App\Filament\Staff\Resources\MileageLogResource;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMileageLog extends EditRecord
{
    protected static string $resource = MileageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => \Illuminate\Support\Facades\Auth::user()?->isManagerOrAdmin()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Auto-fill start_mileage from vehicle record if not provided or empty
        if ((!isset($data['start_mileage']) || $data['start_mileage'] === '' || $data['start_mileage'] === null) && !empty($data['vehicle_id'])) {
            $vehicle = Vehicle::find($data['vehicle_id']);
            $data['start_mileage'] = $vehicle?->current_mileage ?? 0;
        }

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
