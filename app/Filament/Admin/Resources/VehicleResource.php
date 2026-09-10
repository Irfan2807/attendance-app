<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\VehicleResource\Pages;
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
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->isAdmin() ?? false;
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

                Forms\Components\Section::make('Road Tax & Compliance')
                    ->description('Vehicle road tax (LKM) validity and renewal records.')
                    ->schema([
                        Forms\Components\DatePicker::make('road_tax_expiry')
                            ->label('Road Tax Expiry Date')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->placeholder('Select expiration date')
                            ->helperText(fn ($record) => $record?->road_tax_expiry ? $record->roadTaxStatusLabel() : 'Set the official road tax expiry date'),

                        Forms\Components\TextInput::make('road_tax_amount')
                            ->label('Road Tax Cost (RM)')
                            ->numeric()
                            ->prefix('RM')
                            ->step(0.01)
                            ->placeholder('e.g. 180.00'),

                        Forms\Components\FileUpload::make('road_tax_document')
                            ->label('Digital Road Tax / Grant Slip')
                            ->directory('road-taxes')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(10240)
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->columnSpanFull(),
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
                    ->formatStateUsing(fn ($state) => number_format((float) $state) . ' KM'),

                Tables\Columns\TextColumn::make('next_service_mileage')
                    ->label('Next Service')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((float) $state) . ' KM'),

                Tables\Columns\TextColumn::make('km_remaining')
                    ->label('KM Until Service')
                    ->getStateUsing(fn ($record) => $record->serviceMileageDifference())
                    ->formatStateUsing(function ($state): string {
                        if ($state < 0) {
                            return 'Overdue by ' . number_format(abs((float) $state)) . ' KM';
                        }
                        if ((float) $state === 0.0) {
                            return 'Due Now (0 KM)';
                        }

                        return number_format((float) $state) . ' KM';
                    })
                    ->color(fn ($state) => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 500 => 'warning',
                        default => 'success',
                    })
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('road_tax_expiry')
                    ->label('Road Tax Expiry')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Not Set')
                    ->description(fn ($record) => $record->road_tax_expiry ? $record->roadTaxStatusLabel() : null)
                    ->badge()
                    ->color(fn ($record) => $record->roadTaxBadgeColor()),

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
                    ->query(fn ($query) => $query->whereRaw('(next_service_mileage - current_mileage) <= 500')),

                Tables\Filters\Filter::make('service_overdue')
                    ->label('Service Overdue')
                    ->query(fn ($query) => $query->whereRaw('current_mileage >= next_service_mileage')),

                Tables\Filters\Filter::make('road_tax_expiring_soon')
                    ->label('Road Tax Expiring Soon (30 Days)')
                    ->query(fn ($query) => $query->whereNotNull('road_tax_expiry')
                        ->whereBetween('road_tax_expiry', [now()->toDateString(), now()->addDays(30)->toDateString()])),

                Tables\Filters\Filter::make('road_tax_expired')
                    ->label('Road Tax Expired')
                    ->query(fn ($query) => $query->whereNotNull('road_tax_expiry')
                        ->where('road_tax_expiry', '<', now()->toDateString())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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

