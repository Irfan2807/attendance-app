<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\StaffAttendanceOverviewResource\Pages;
use App\Models\Attendance;
use App\Services\AttendanceMetricsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StaffAttendanceOverviewResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Staff Attendance Overview';
    protected static ?string $navigationGroup = 'Management';
    protected static ?string $slug = 'staff-attendance-overview';
    protected static ?string $modelLabel = 'Staff Attendance';

    public static function canViewAny(): bool
    {
        // Only Managers (role 2) can view staff overview
        return Auth::user() && Auth::user()->role === 2;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        // Managers can edit approval notes
        return Auth::user() && Auth::user()->role === 2;
    }

    public static function canDelete($record): bool
    {
        return Auth::user() && Auth::user()->role === 2;
    }

    public static function getEloquentQuery(): Builder
    {
        // Show only role-3 (Staff) attendance records, excluding the manager's own records.
        return parent::getEloquentQuery()
            ->with(['user', 'approver'])
            ->whereHas('user', fn ($q) => $q->where('role', 3))
            ->orderByDesc('clock_in_time');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Attendance Details')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('Staff Name')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn($state, $record) => $record?->user?->name ?? '—')
                            ->default(fn($record) => $record?->user?->name ?? '—'),

                        Forms\Components\TextInput::make('user.phone')
                            ->label('Phone')
                            ->disabled(),

                        Forms\Components\TextInput::make('site_name')
                            ->label('Location')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('clock_in_time')
                            ->label('Clock In')
                            ->displayFormat('d/m/Y H:i:s')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('clock_out_time')
                            ->label('Clock Out')
                            ->displayFormat('d/m/Y H:i:s')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending Approval',
                                'temporary' => 'Temporary (Clocked Out)',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'completed' => 'Completed',
                            ])
                            ->disabled()
                            ->native(false),

                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Verification Notes')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('approval_notes')
                            ->label('Approval Notes')
                            ->columnSpanFull(),
                    ])->columns(1),

                Forms\Components\Section::make('Location Data')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->disabled()
                            ->default(fn ($record) => $record->latitude ?? '0'),

                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->disabled()
                            ->default(fn ($record) => $record->longitude ?? '0'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Staff')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('site_name')
                    ->label('Location')
                    ->searchable(),

                Tables\Columns\TextColumn::make('clock_in_time')
                    ->label('Clock In')
                    ->dateTime('d/m H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('clock_out_time')
                    ->label('Clock Out')
                    ->dateTime('H:i')
                    ->formatStateUsing(fn($state) => $state ? $state->format('H:i') : 'Active'),

                Tables\Columns\TextColumn::make('hours_worked')
                    ->label('Duration')
                    ->getStateUsing(fn (Attendance $record) => AttendanceMetricsService::formatMinutes(
                        AttendanceMetricsService::workedMinutes($record)
                    ))
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('overtime_minutes')
                    ->label('Overtime')
                    ->getStateUsing(fn (Attendance $record) => AttendanceMetricsService::formatMinutes(
                        AttendanceMetricsService::overtimeMinutes($record)
                    ))
                    ->badge()
                    ->color(fn (string $state) => $state === '0m' ? 'gray' : 'success'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'temporary',
                        'success' => fn ($state) => in_array($state, ['approved', 'completed']),
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending Approval',
                        'temporary' => 'Awaiting Clock Out',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                    ]),

                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->label('Staff Member'),

                Tables\Filters\SelectFilter::make('site_name')
                    ->label('Location')
                    ->options(fn() => Attendance::distinct('site_name')->pluck('site_name', 'site_name')->toArray()),

                Tables\Filters\Filter::make('clock_in_time')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $q) => $q->whereDate('clock_in_time', '>=', $data['from'])
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $q) => $q->whereDate('clock_in_time', '<=', $data['until'])
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('clock_in_time', 'desc')
            ->paginated([10, 25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaffAttendanceOverview::route('/'),
            'view' => Pages\ViewStaffAttendanceOverview::route('/{record}'),
            'edit' => Pages\EditStaffAttendanceOverview::route('/{record}/edit'),
        ];
    }
}
