<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\MileageLogResource\Pages;
use App\Models\MileageLog;
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
    protected static ?string $navigationLabel = 'Mileage Logs';
    protected static ?string $navigationGroup = 'Fleet';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        // Both managers and staff can view
        return Auth::user() && in_array(Auth::user()->role, [2, 3]);
    }

    public static function canCreate(): bool
    {
        // Both staff and managers can log mileage
        return Auth::user() && in_array(Auth::user()->role, [2, 3]);
    }

    public static function canEdit($record): bool
    {
        // Only managers can edit logs, or staff can edit their own logs within 24 hours
        $user = Auth::user();
        if ($user->role === 2) {
            return true; // Managers can edit any
        }
        // Staff can edit their own within 24 hours
        return $record->user_id === $user->id && $record->created_at->gt(now()->subDay());
    }

    public static function canDelete($record): bool
    {
        // Only managers can delete
        return Auth::user() && Auth::user()->role === 2;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Mileage Entry')
                    ->schema([
                        Forms\Components\Select::make('vehicle_id')
                            ->label('Vehicle')
                            ->options(Vehicle::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateHydrated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $vehicle = Vehicle::find($state);
                                    $set('current_vehicle_mileage', $vehicle?->current_mileage ?? 0);
                                }
                            })
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $vehicle = Vehicle::find($state);
                                    $set('current_vehicle_mileage', $vehicle?->current_mileage ?? 0);
                                }
                            })
                            ->helperText(fn($get) => $get('current_vehicle_mileage') 
                                ? 'Current mileage: ' . number_format($get('current_vehicle_mileage')) . ' KM' 
                                : null),

                        Forms\Components\Hidden::make('current_vehicle_mileage'),

                        Forms\Components\TextInput::make('mileage_reading')
                            ->label('Odometer Reading (KM)')
                            ->required()
                            ->numeric()
                            ->minValue(fn ($get) => (int) ($get('current_vehicle_mileage') ?: 0))
                            ->suffix('KM')
                            ->helperText('Enter the current odometer reading from the vehicle'),

                        Forms\Components\DateTimePicker::make('recorded_at')
                            ->label('Date & Time')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->placeholder('Optional: Trip details, fuel, etc.'),
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
                    ->sortable(),

                Tables\Columns\TextColumn::make('vehicle.name')
                    ->label('Model')
                    ->searchable(),

                Tables\Columns\TextColumn::make('mileage_reading')
                    ->label('Mileage')
                    ->sortable()
                    ->formatStateUsing(fn($state) => number_format($state) . ' KM'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Logged By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('recorded_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(30)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vehicle_id')
                    ->label('Vehicle')
                    ->options(Vehicle::pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->visible(fn() => Auth::user()->role === 2),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => self::canEdit($record)),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => Auth::user()->role === 2),
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
