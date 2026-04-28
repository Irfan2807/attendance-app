<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\StaffUserResource\Pages;
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
                        ->formatStateUsing(fn (int $state): string => match ($state) {
                            1 => 'Super Admin',
                            2 => 'Manager',
                            3 => 'Staff',
                            default => 'Unknown',
                        })
                        ->color(fn (int $state): string => match ($state) {
                            1 => 'danger',
                            2 => 'warning',
                            3 => 'success',
                            default => 'gray',
                        }),

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
                        ->state(fn (User $record): string => $record->attendances()
                            ->latest('clock_in_time')
                            ->value('clock_in_time') ?? 'No records')
                        ->dateTime('d M Y h:i A'),

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

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(fn ($query) => $query->withCount('attendances')->with(['attendances' => fn ($q) => $q->whereNotNull('clock_out_time')]))
            ->filters([
                //
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
            'view' => Pages\ViewStaffUser::route('/{record}'),
        ];
    }
}

