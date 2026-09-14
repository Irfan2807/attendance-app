<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\PublicHolidayResource\Pages;
use App\Models\PublicHoliday;
use App\Services\HolidayService;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PublicHolidayResource extends Resource
{
    protected static ?string $model = PublicHoliday::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Public Holidays';
    protected static ?string $navigationGroup = 'Company';
    protected static ?string $slug = 'public-holidays';
    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        $defaultState = HolidayService::defaultState();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('day_name')
                    ->label('Day')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Holiday Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('coverage')
                    ->label('Type')
                    ->badge()
                    ->getStateUsing(fn (PublicHoliday $record): string => $record->is_nationwide ? 'Nationwide' : 'State Holiday')
                    ->color(fn (string $state): string => $state === 'Nationwide' ? 'success' : 'info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function (PublicHoliday $record): string {
                        $today = now()->startOfDay();
                        $date = $record->date->copy()->startOfDay();
                        if ($date->lt($today)) {
                            return 'Passed';
                        }
                        if ($date->equalTo($today)) {
                            return 'Today';
                        }
                        $days = (int) $today->diffInDays($date);
                        return "In {$days} days";
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Today' => 'success',
                        'Passed' => 'gray',
                        default => 'warning',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('year')
                    ->options(fn () => PublicHoliday::distinct()->pluck('year', 'year')->all())
                    ->default(now()->year),

                Tables\Filters\TernaryFilter::make('applicable_to_us')
                    ->label('Our State Only (' . $defaultState . ')')
                    ->queries(
                        true: fn ($query) => $query->forState($defaultState),
                        false: fn ($query) => $query,
                    )
                    ->default(true),
            ])
            ->defaultSort('date', 'asc')
            ->paginated([25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPublicHolidays::route('/'),
        ];
    }
}
