<?php

namespace App\Filament\Staff\Resources\PublicHolidayResource\Pages;

use App\Filament\Staff\Resources\PublicHolidayResource;
use App\Services\HolidayService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListPublicHolidays extends ListRecords
{
    protected static string $resource = PublicHolidayResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();

        // Only HR Executives and Directors/Admins can trigger sync
        if (! $user?->isHr() && ! $user?->isAdmin()) {
            return [];
        }

        return [
            Actions\Action::make('sync_holidays')
                ->label('Sync Holidays from API')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Sync Malaysia Public Holidays')
                ->modalDescription('Fetch latest public holidays from the official API into the company calendar.')
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
        ];
    }
}

