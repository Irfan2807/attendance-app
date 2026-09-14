<?php

namespace App\Filament\Admin\Resources\PublicHolidayResource\Pages;

use App\Filament\Admin\Resources\PublicHolidayResource;
use App\Services\HolidayService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPublicHolidays extends ListRecords
{
    protected static string $resource = PublicHolidayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync_holidays')
                ->label('Sync Holidays from API')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Sync Malaysia Public Holidays')
                ->modalDescription('Fetch and update public holidays for the current year from the official API.')
                ->action(function () {
                    $year = now()->year;
                    $count = HolidayService::syncHolidays($year);

                    if ($count > 0) {
                        Notification::make()
                            ->title("Successfully synced {$count} public holidays for {$year}!")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Failed to sync public holidays from API')
                            ->danger()
                            ->send();
                    }
                }),

            Actions\CreateAction::make()
                ->label('Add Holiday Manually'),
        ];
    }
}

