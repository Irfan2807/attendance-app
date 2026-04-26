<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Staff Management';

    // 1. Restrict access: Only Admins (1) and Managers (2) can see this page.
    public static function canViewAny(): bool
    {
        // Safe check: If no user (null), return false immediately.
        return Auth::user() && in_array(Auth::user()->role, [1, 2]);
    }

    // 2. Creation: Only Admins (1) and Managers (2) can create.
    public static function canCreate(): bool
    {
        return Auth::user() && in_array(Auth::user()->role, [1, 2]);
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

                // 3. Smart Role Selection
                Forms\Components\Select::make('role')
                    ->options(function () {
                        // usage of '?->' ensures we don't crash if user is somehow null
                        if (Auth::user()?->role === 1) {
                            return [
                                1 => 'Super Admin',
                                2 => 'Manager',
                                3 => 'Staff',
                            ];
                        }
                        // Default for Managers (or if logic fails safe)
                        return [
                            3 => 'Staff',
                        ];
                    })
                    ->default(3)
                    ->required()
                    ->dehydrated(),

                // 4. Password Hashing (CRITICAL: Do not remove this!)
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        1 => 'Super Admin',
                        2 => 'Manager',
                        3 => 'Staff',
                    ]),
            ])
            ->actions([
                // 5. Edit Permission: Managers can only edit Staff
                // usage of '?->' makes this null-safe
                Tables\Actions\EditAction::make()
                    ->visible(fn (User $record) => 
                        Auth::user()?->role === 1 || 
                        (Auth::user()?->role === 2 && $record->role === 3)
                    ),

                // 6. Delete Permission: Managers can only delete Staff
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (User $record) => 
                        Auth::user()?->role === 1 || 
                        (Auth::user()?->role === 2 && $record->role === 3)
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->role === 1),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}