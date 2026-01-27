<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\StaffAttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StaffAttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Attendance Logs';
    protected static ?string $navigationGroup = 'My Shift';
    protected static ?string $slug = 'attendance-logs';
    protected static ?string $modelLabel = 'Attendance Log';

    public static function canViewAny(): bool
    {
        // Both staff (3) and managers (2) can view
        // Staff see only their own logs
        // Managers see only their own logs here (all staff logs are in StaffAttendanceOverviewResource)
        return Auth::user() && in_array(Auth::user()->role, [2, 3]);
    }

    public static function canCreate(): bool
    {
        return false; // Cannot manually create attendance records
    }

    public static function canEdit($record): bool
    {
        // Only managers can edit (for approval notes, etc.)
        return Auth::user() && Auth::user()->role === 2;
    }

    public static function canDelete($record): bool
    {
        // Only managers can delete attendance records
        return Auth::user() && Auth::user()->role === 2;
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        // Eager load relationships for better performance
        $query = parent::getEloquentQuery()->with(['user', 'approver']);

        // Both staff and managers see only their own attendance in this resource
        // Managers see all staff attendance in StaffAttendanceOverviewResource instead
        return $query
            ->where('user_id', $user->id)
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
                            ->disabled()
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

                Tables\Columns\TextColumn::make('site_name')
                    ->label('Location')
                    ->searchable(),

                Tables\Columns\TextColumn::make('clock_in_time')
                    ->label('Clock In')
                    ->dateTime('H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('clock_out_time')
                    ->label('Clock Out')
                    ->dateTime('H:i:s')
                    ->formatStateUsing(fn($state) => $state ? $state->format('H:i:s') : 'Not clocked out')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'info' => 'completed',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => Auth::user()->role === 2), // Only managers can delete
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('clock_in_time', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaffAttendance::route('/'),
            'view' => Pages\ViewStaffAttendance::route('/{record}'),
        ];
    }
}
