<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PublicHolidayResource\Pages;
use App\Models\PublicHoliday;
use App\Services\HolidayService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PublicHolidayResource extends Resource
{
    protected static ?string $model = PublicHoliday::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Public Holidays';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $slug = 'public-holidays';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Holiday Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\DatePicker::make('date')
                    ->label('Date')
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('day_name')
                    ->label('Day of Week')
                    ->placeholder('e.g. Monday'),

                Forms\Components\Toggle::make('is_nationwide')
                    ->label('Applies Nationwide (All States)')
                    ->default(true),

                Forms\Components\TagsInput::make('state_codes')
                    ->label('State Codes')
                    ->placeholder('e.g. SGR, KUL, JHR')
                    ->helperText('State codes where this holiday applies (leave empty if nationwide)'),

                Forms\Components\TextInput::make('year')
                    ->numeric()
                    ->default(now()->year)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
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
                    ->label('Coverage')
                    ->badge()
                    ->getStateUsing(fn (PublicHoliday $record): string => $record->is_nationwide ? 'Nationwide' : 'State-Specific')
                    ->color(fn (string $state): string => $state === 'Nationwide' ? 'success' : 'info'),

                Tables\Columns\TextColumn::make('state_codes')
                    ->label('States')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('All States')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('year')
                    ->options(fn () => PublicHoliday::distinct()->pluck('year', 'year')->all())
                    ->default(now()->year),

                Tables\Filters\TernaryFilter::make('is_nationwide')
                    ->label('Nationwide Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPublicHolidays::route('/'),
        ];
    }
}
