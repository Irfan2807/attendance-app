<?php

namespace App\Filament\Staff\Resources\StaffSiteResource\Pages;

use App\Filament\Staff\Resources\StaffSiteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSite extends EditRecord
{
    protected static string $resource = StaffSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Site updated')
            ->body('The site has been updated successfully.');
    }
}
