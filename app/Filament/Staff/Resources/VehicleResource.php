<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Vehicles';
    protected static ?string $navigationGroup = 'Fleet';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        // Both managers and staff can view
        return Auth::user() && in_array(Auth::user()->role, [2, 3]);
    }

    public static function canCreate(): bool
    {
        // Only managers can create vehicles
        return Auth::user() && Auth::user()->role === 2;
    }

    public static function canEdit($record): bool
    {
        // Only managers can edit vehicles
        return Auth::user() && Auth::user()->role === 2;
    }

    public static function canDelete($record): bool
    {
        // Only managers can delete vehicles
        return Auth::user() && Auth::user()->role === 2;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Vehicle Information')
                    ->schema([
                        Forms\Components\TextInput::make('numberplate')
                            ->label('Number Plate')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('ABC1234'),

                        Forms\Components\TextInput::make('name')
                            ->label('Vehicle Name/Model')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Toyota Hilux'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(3),

                Forms\Components\Section::make('Mileage Tracking')
                    ->schema([
                        Forms\Components\TextInput::make('current_mileage')
                            ->label('Current Mileage (KM)')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('KM'),

                        Forms\Components\TextInput::make('next_service_mileage')
                            ->label('Next Service at (KM)')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('KM')
                            ->helperText('Set when service is due'),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numberplate')
                    ->label('Number Plate')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Vehicle')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_mileage')
                    ->label('Current Mileage')
                    ->sortable()
                    ->formatStateUsing(fn($state) => number_format($state) . ' KM'),

                Tables\Columns\TextColumn::make('next_service_mileage')
                    ->label('Next Service')
                    ->sortable()
                    ->formatStateUsing(fn($state) => number_format($state) . ' KM'),

                Tables\Columns\TextColumn::make('km_remaining')
                    ->label('KM Until Service')
                    ->getStateUsing(fn($record) => $record->kmUntilService())
                    ->formatStateUsing(fn($state) => number_format($state) . ' KM')
                    ->color(fn($state) => match(true) {
                        $state <= 0 => 'danger',
                        $state <= 500 => 'warning',
                        default => 'success',
                    })
                    ->weight('bold'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All vehicles')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\Filter::make('service_due_soon')
                    ->label('Service Due Soon')
                    ->query(fn($query) => $query->whereRaw('(next_service_mileage - current_mileage) <= 500')),

                Tables\Filters\Filter::make('service_overdue')
                    ->label('Service Overdue')
                    ->query(fn($query) => $query->whereRaw('current_mileage >= next_service_mileage')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn() => Auth::user()->role === 2),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('numberplate');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
            'view' => Pages\ViewVehicle::route('/{record}'),
        ];
    }
}
