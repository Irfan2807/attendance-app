<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SiteResource\Pages;
use App\Models\Site;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    // 1. Change the Icon
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    // 2. Change the Menu Label (Sidebar)
    protected static ?string $navigationLabel = 'Office Locations';

    // 3. Change the URL (e.g. /admin/locations instead of /admin/sites)
    protected static ?string $slug = 'office-locations';

    // 4. Change the "Singular" name (e.g. "Create Office Location")
    protected static ?string $modelLabel = 'Office Location';

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

                Forms\Components\TextInput::make('ip_address')
                    ->label('Office IP Address')
                    ->helperText('Optional. If set, clock-ins from this IP will be auto-verified.'),

                Forms\Components\TextInput::make('radius_meters')
                    ->default(100)
                    ->suffix('meters')
                    ->label('Allowed Radius')
                    ->placeholder('e.g. 100')
                    ->rules(['regex:/^\d+$/'])
                    ->required(),

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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'create' => Pages\CreateSite::route('/create'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
        ];
    }
}