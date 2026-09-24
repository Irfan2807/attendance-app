<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\MileageLogResource\Pages;
use App\Models\MileageLog;
use App\Models\Site;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MileageLogResource extends Resource
{
    protected static ?string $model = MileageLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Vehicle Trip Logs';
    protected static ?string $navigationGroup = 'Fleet';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Trip Log';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['vehicle', 'user', 'site']);
    }

    public static function canViewAny(): bool
    {
        return Auth::check();
    }

    public static function canCreate(): bool
    {
        return Auth::check();
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if ($user->isManagerOrAdmin()) {
            return true;
        }

        // Staff can edit their own logs within 24 hours
        return $record->user_id === $user->id && $record->created_at->gt(now()->subDay());
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->isManagerOrAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Vehicle & Journey Details')
                    ->description('Log your company vehicle trip upon return to maintain accurate vehicle telemetry and site records.')
                    ->schema([
                        Forms\Components\Select::make('vehicle_id')
                            ->label('Company Vehicle')
                            ->options(Vehicle::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateHydrated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state && blank($get('start_mileage'))) {
                                    $vehicle = Vehicle::find($state);
                                    $set('start_mileage', $vehicle?->current_mileage ?? 0);
                                }
                            })
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $vehicle = Vehicle::find($state);
                                    $set('start_mileage', $vehicle?->current_mileage ?? 0);
                                }
                            })
                            ->helperText('Select the company vehicle used for this trip.'),

                        Forms\Components\Select::make('destination_type')
                            ->label('Destination Type')
                            ->options([
                                'registered_site' => 'Registered Company Work Site',
                                'custom_location' => 'Client Project / Other Location',
                            ])
                            ->default('registered_site')
                            ->reactive()
                            ->dehydrated(false),

                        Forms\Components\Select::make('site_id')
                            ->label('Work Site Destination')
                            ->options(Site::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->visible(fn (Forms\Get $get) => $get('destination_type') !== 'custom_location')
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $site = Site::find($state);
                                    $set('destination', $site?->name);
                                }
                            })
                            ->helperText('Select the assigned site visited.'),

                        Forms\Components\TextInput::make('destination')
                            ->label('Client Site / Location Name')
                            ->placeholder('e.g. Petronas Gas Processing Plant, Kerteh')
                            ->visible(fn (Forms\Get $get) => $get('destination_type') === 'custom_location' || blank($get('site_id')))
                            ->required(fn (Forms\Get $get) => $get('destination_type') === 'custom_location')
                            ->maxLength(255),

                        Forms\Components\Select::make('purpose')
                            ->label('Trip Purpose')
                            ->options([
                                'Site Survey & Inspection' => 'Site Survey & Inspection',
                                'Emergency Maintenance' => 'Emergency Maintenance',
                                'CME & Telecom Rigging' => 'CME & Telecom Rigging',
                                'Fiber Splicing & Rollout' => 'Fiber Splicing & Rollout',
                                'Material & Tool Transport' => 'Material & Tool Transport',
                                'Routine Vehicle Servicing' => 'Routine Vehicle Servicing',
                                'Client Meeting & Briefing' => 'Client Meeting & Briefing',
                                'Other' => 'Other / General Transit',
                            ])
                            ->required()
                            ->searchable()
                            ->default('Site Survey & Inspection'),

                        Forms\Components\DateTimePicker::make('recorded_at')
                            ->label('Trip Date & Time')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),
                    ])->columns(2),

                Forms\Components\Section::make('Mileage & Odometer Readings')
                    ->schema([
                        Forms\Components\TextInput::make('start_mileage')
                            ->label('Starting Odometer (KM)')
                            ->required()
                            ->numeric()
                            ->suffix('KM')
                            ->reactive()
                            ->helperText('Odometer before departure (prefilled from vehicle record).'),

                        Forms\Components\TextInput::make('end_mileage')
                            ->label('Ending Odometer (KM)')
                            ->required()
                            ->numeric()
                            ->suffix('KM')
                            ->reactive()
                            ->minValue(fn (Forms\Get $get) => (int) ($get('start_mileage') ?: 0))
                            ->helperText(function (Forms\Get $get) {
                                $start = (int) $get('start_mileage');
                                $end = (int) $get('end_mileage');
                                if ($end > 0 && $end >= $start) {
                                    return '✅ Trip distance: ' . number_format($end - $start) . ' KM';
                                }
                                return 'Enter current odometer reading after parking.';
                            }),

                        Forms\Components\Textarea::make('notes')
                            ->label('Trip Notes & Fuel / Toll Remarks')
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->placeholder('Optional: Fuel refill liters, toll expenses, vehicle condition remarks, etc.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.numberplate')
                    ->label('Vehicle')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Model')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Driver')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('resolved_destination')
                    ->label('Destination')
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->where('destination', 'like', "%{$search}%")
                            ->orWhereHas('site', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    )
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('purpose')
                    ->label('Purpose')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('start_mileage')
                    ->label('Start KM')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state) . ' KM' : '—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('end_mileage')
                    ->label('End KM')
                    ->numeric()
                    ->getStateUsing(fn ($record) => $record->end_mileage ?? $record->mileage_reading)
                    ->formatStateUsing(fn ($state) => number_format((float) $state) . ' KM')
                    ->sortable(),

                Tables\Columns\TextColumn::make('distance_km')
                    ->label('Distance')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(fn ($record) => $record->distance_km !== null ? '+' . number_format((float) $record->distance_km) . ' KM' : '—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('recorded_at')
                    ->label('Trip Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vehicle_id')
                    ->label('Vehicle')
                    ->options(Vehicle::pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->visible(fn () => Auth::user()?->isManagerOrAdmin()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => self::canEdit($record)),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => Auth::user()?->isManagerOrAdmin()),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('recorded_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMileageLogs::route('/'),
            'create' => Pages\CreateMileageLog::route('/create'),
            'edit' => Pages\EditMileageLog::route('/{record}/edit'),
            'view' => Pages\ViewMileageLog::route('/{record}'),
        ];
    }
}
