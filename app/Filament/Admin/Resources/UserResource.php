<?php

namespace App\Filament\Admin\Resources;

use App\Enums\Role;
use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AttendanceMetricsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
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

    // 1. Restrict access: Only Admins and Managers can see this page.
    public static function canViewAny(): bool
    {
        return Auth::user()?->isManagerOrAdmin() ?? false;
    }

    // 2. Creation: Only Admins and Managers can create.
    public static function canCreate(): bool
    {
        return Auth::user()?->isManagerOrAdmin() ?? false;
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
                        if (Auth::user()?->isAdmin()) {
                            return Role::options();
                        }

                        return [
                            Role::Staff->value => Role::Staff->label(),
                        ];
                    })
                    ->default(Role::Staff->value)
                    ->required()
                    ->dehydrated(),

                Forms\Components\Select::make('manager_id')
                    ->label('Reporting Manager')
                    ->relationship('manager', 'name', fn ($q) => $q->where('role', Role::Manager->value))
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Assign operational supervisor for this employee'),

                // 4. Password Hashing (CRITICAL: Do not remove this!)
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->minLength(8),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active Status')
                    ->default(true)
                    ->helperText('Inactive staff are excluded from daily attendance rates')
                    ->required(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Staff Details')
                ->schema([
                    Infolists\Components\TextEntry::make('name')
                        ->label('Full Name'),

                    Infolists\Components\TextEntry::make('phone')
                        ->label('Phone Number'),

                    Infolists\Components\TextEntry::make('role')
                        ->label('Role')
                        ->badge()
                        ->formatStateUsing(fn ($state): string => Role::tryFrom($state instanceof Role ? $state->value : (int) $state)?->label() ?? 'Unknown')
                        ->color(fn ($state): string => match ($state instanceof Role ? $state->value : (int) $state) {
                            Role::SuperAdmin->value => 'danger',
                            Role::Manager->value => 'warning',
                            Role::Staff->value => 'success',
                            Role::HR->value => 'info',
                            default => 'gray',
                        }),

                    Infolists\Components\TextEntry::make('manager.name')
                        ->label('Reporting Manager')
                        ->placeholder('Unassigned'),

                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Member Since')
                        ->dateTime('d M Y'),
                ])->columns(2),

            Infolists\Components\Section::make('Attendance Summary')
                ->schema([
                    Infolists\Components\TextEntry::make('total_sessions')
                        ->label('Total Sessions')
                        ->state(fn (User $record): int => $record->attendances()->count())
                        ->badge()
                        ->color('info'),

                    Infolists\Components\TextEntry::make('total_hours_worked')
                        ->label('Total Hours Worked')
                        ->state(function (User $record): string {
                            $minutes = $record->attendances()
                                ->whereNotNull('clock_out_time')
                                ->get()
                                ->sum(fn (Attendance $a) => AttendanceMetricsService::workedMinutes($a));
                            return AttendanceMetricsService::formatMinutes($minutes);
                        })
                        ->badge()
                        ->color('success'),

                    Infolists\Components\TextEntry::make('this_month_sessions')
                        ->label('Sessions This Month')
                        ->state(fn (User $record): int => $record->attendances()
                            ->whereYear('clock_in_time', now()->year)
                            ->whereMonth('clock_in_time', now()->month)
                            ->count())
                        ->badge()
                        ->color('warning'),

                    Infolists\Components\TextEntry::make('this_month_hours')
                        ->label('Hours This Month')
                        ->state(function (User $record): string {
                            $minutes = $record->attendances()
                                ->whereYear('clock_in_time', now()->year)
                                ->whereMonth('clock_in_time', now()->month)
                                ->whereNotNull('clock_out_time')
                                ->get()
                                ->sum(fn (Attendance $a) => AttendanceMetricsService::workedMinutes($a));
                            return AttendanceMetricsService::formatMinutes($minutes);
                        })
                        ->badge()
                        ->color('warning'),

                    Infolists\Components\TextEntry::make('last_attendance')
                        ->label('Last Attendance')
                        ->state(fn (User $record): ?string => $record->attendances()
                            ->latest('clock_in_time')
                            ->value('clock_in_time'))
                        ->dateTime('d M Y h:i A')
                        ->placeholder('No records'),

                    Infolists\Components\TextEntry::make('incomplete_clock_out_count')
                        ->label('Incomplete Clock-Outs')
                        ->badge()
                        ->color(fn ($state): string => ($state ?? 0) > 0 ? 'danger' : 'success'),
                ])->columns(2),
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
                    ->formatStateUsing(fn ($state): string => Role::tryFrom($state instanceof Role ? $state->value : (int) $state)?->label() ?? 'Staff')
                    ->color(fn ($state): string => match ($state instanceof Role ? $state->value : (int) $state) {
                        Role::SuperAdmin->value => 'danger',
                        Role::Manager->value => 'warning',
                        Role::Staff->value => 'success',
                        Role::HR->value => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('manager.name')
                    ->label('Reporting Manager')
                    ->placeholder('Unassigned')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_sessions')
                    ->label('Sessions')
                    ->getStateUsing(fn (User $record): int => $record->attendances_count ?? $record->attendances()->count())
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_hours_worked')
                    ->label('Total Hours')
                    ->getStateUsing(function (User $record): string {
                        $attendances = $record->relationLoaded('attendances')
                            ? $record->attendances
                            : $record->attendances()->whereNotNull('clock_out_time')->get();
                        $minutes = $attendances
                            ->whereNotNull('clock_out_time')
                            ->sum(fn (Attendance $a) => AttendanceMetricsService::workedMinutes($a));
                        return AttendanceMetricsService::formatMinutes($minutes);
                    })
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn ($query) => $query->withCount('attendances')->with(['manager', 'attendances' => fn ($q) => $q->whereNotNull('clock_out_time')]))
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options(Role::options()),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

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
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
