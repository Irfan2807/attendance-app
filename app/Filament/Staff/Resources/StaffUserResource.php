<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\StaffUserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class StaffUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Staff Management';
    protected static ?string $navigationGroup = 'Company';
    protected static ?string $slug = 'staff';
    protected static ?string $modelLabel = 'Staff';

    public static function canViewAny(): bool
    {
        return Auth::user() && Auth::user()->role === 2;
    }

    public static function canCreate(): bool
    {
        return Auth::user() && Auth::user()->role === 2;
    }

    public static function getEloquentQuery(): Builder
    {
        // Managers may only view and manage role-3 (Staff) users.
        return parent::getEloquentQuery()->where('role', 3);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('Phone Number')
                    ->placeholder('0123456789')
                    ->minLength(10)
                    ->maxLength(11)
                    ->regex('/^01[0-9]{8,9}$/')
                    ->helperText('Malaysian mobile number (e.g., 0123456789)')
                    ->validationMessages([
                        'regex' => 'Phone number must start with 01 and be 10-11 digits.',
                    ]),

                Forms\Components\Select::make('role')
                    ->options(function () {
                        if (Auth::user()?->role === 1) {
                            return [
                                1 => 'Super Admin',
                                2 => 'Manager',
                                3 => 'Staff',
                            ];
                        }
                        return [
                            3 => 'Staff',
                        ];
                    })
                    ->default(3)
                    ->required()
                    ->dehydrated(),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->minLength(8),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->label('Phone Number'),

                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => 'Super Admin',
                        2 => 'Manager',
                        3 => 'Staff',
                        default => 'Staff',
                    })
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'danger',
                        2 => 'warning',
                        3 => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListStaffUsers::route('/'),
            'create' => Pages\CreateStaffUser::route('/create'),
        ];
    }
}
