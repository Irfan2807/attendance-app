<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\StaffSiteResource\Pages;
use App\Models\Site;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class StaffSiteResource extends Resource
{
    protected static ?string $model = Site::class;

    // Use same icon/label as admin but scoped to staff panel
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Office Locations';
    protected static ?string $navigationGroup = 'Company';
    protected static ?string $slug = 'office-locations';
    protected static ?string $modelLabel = 'Office Location';

    public static function canViewAny(): bool
    {
        return Auth::user() && Auth::user()->role === 2;
    }

    public static function canCreate(): bool
    {
        // Site creation and editing is restricted to the admin panel (Super Admin only).
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Site Name')
                    ->placeholder('e.g. HQ Office'),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->required()
                            ->label('Latitude')
                            ->placeholder('e.g. 3.068215')
                            ->rules(['regex:/^-?\d+\.?\d*$/']),
                        Forms\Components\TextInput::make('longitude')
                            ->required()
                            ->label('Longitude')
                            ->placeholder('e.g. 101.562021')
                            ->rules(['regex:/^-?\d+\.?\d*$/']),
                    ]),

                Forms\Components\TextInput::make('radius_meters')
                    ->default(100)
                    ->suffix('meters')
                    ->label('Allowed Radius')
                    ->placeholder('e.g. 100')
                    ->rules(['regex:/^\d+$/'])
                    ->required(),

                Forms\Components\TextInput::make('ip_address')
                    ->label('Office IP Address')
                    ->helperText('Optional. If set, clock-ins from this IP will be auto-verified.'),

                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->label('Active Site'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('latitude'),
                Tables\Columns\TextColumn::make('longitude'),
                Tables\Columns\TextColumn::make('radius_meters')
                    ->suffix('m'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('map')
                    ->label('Map')
                    ->icon('heroicon-o-map-pin')
                    ->color('primary')
                    ->url(fn($record) => "https://www.google.com/maps?q={$record->latitude},{$record->longitude}")
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSites::route('/'),
        ];
    }
}
