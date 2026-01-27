<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\StaffAttendanceApprovalResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StaffAttendanceApprovalResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationLabel = 'Clock-In Approvals';
    protected static ?string $navigationGroup = 'Management';
    protected static ?string $slug = 'clock-in-approvals';
    protected static ?string $modelLabel = 'Clock-In Approval';

    public static function canViewAny(): bool
    {
        // Only Managers (role 2) can approve, not staff (role 3)
        return Auth::user() && Auth::user()->role === 2;
    }

    public static function canCreate(): bool
    {
        return false; // Cannot create approvals manually
    }

    public static function getEloquentQuery(): Builder
    {
        // Show only pending and temporary entries, excluding the current manager's own records
        // Managers can only approve other managers' clock-ins (peer approval), not their own
        return parent::getEloquentQuery()
            ->with(['user', 'approver'])
            ->whereIn('status', ['pending', 'temporary'])
            ->where('user_id', '!=', Auth::id())
            ->whereHas('user', fn (Builder $q) => $q->where('role', 2))
            ->orderByDesc('created_at');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Attendance Details')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('Staff Name')
                            ->disabled(),

                        Forms\Components\TextInput::make('user.phone')
                            ->label('Phone')
                            ->disabled(),

                        Forms\Components\TextInput::make('site_name')
                            ->disabled(),

                        Forms\Components\TextInput::make('clock_in_time')
                            ->type('datetime-local')
                            ->disabled(),

                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Verification Notes')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Location Details')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Approval Decision')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'approved' => 'Approve',
                                'rejected' => 'Reject',
                            ])
                            ->required()
                            ->native(false),
                    ])->columns(1),
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
                    ->label('Clock In Time')
                    ->dateTime('H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('clock_out_time')
                    ->label('Clock Out Time')
                    ->dateTime('H:i:s')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('hours_worked')
                    ->label('Hours')
                    ->getStateUsing(function ($record) {
                        if (!$record->clock_out_time) {
                            return '—';
                        }
                        $hours = $record->clock_in_time->diffInHours($record->clock_out_time);
                        $minutes = $record->clock_in_time->diffInMinutes($record->clock_out_time) % 60;
                        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
                    })
                    ->badge()
                    ->color(fn ($state) => $state === '—' ? 'gray' : 'success'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('verification_notes')
                    ->label('Notes')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('✓ Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->form([
                        Forms\Components\Textarea::make('approval_notes')
                            ->label('Approval Notes')
                            ->placeholder('Optional notes for the employee record')
                            ->maxLength(500),
                    ])
                    ->action(function ($record, array $data) {
                        $approver = Auth::user();
                        
                        // Additional safety check: prevent self-approval
                        if ($record->user_id === $approver->id) {
                            \Filament\Notifications\Notification::make()
                                ->title('Cannot Approve Own Attendance')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        $record->update([
                            'status' => 'approved',
                            'approval_notes' => $data['approval_notes'] ?? null,
                            'approved_by' => $approver->id,
                            'approved_at' => now(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Attendance Approved')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('✗ Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->form([
                        Forms\Components\Textarea::make('approval_notes')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function ($record, array $data) {
                        $approver = Auth::user();
                        
                        // Additional safety check: prevent self-rejection
                        if ($record->user_id === $approver->id) {
                            \Filament\Notifications\Notification::make()
                                ->title('Cannot Reject Own Attendance')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        // Keep the record but mark as rejected with notes
                        $record->update([
                            'status' => 'rejected',
                            'approval_notes' => $data['approval_notes'] ?? null,
                            'approved_by' => $approver->id,
                            'approved_at' => now(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Attendance Rejected')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('✓ Approve Selected')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->modalHeading('Approve Selected Attendance Records')
                        ->modalDescription('Are you sure you want to approve these attendance records?')
                        ->action(function ($records) {
                            $approver = Auth::user();
                            $count = 0;
                            
                            foreach ($records as $record) {
                                // Skip own records
                                if ($record->user_id === $approver->id) {
                                    continue;
                                }
                                
                                $record->update([
                                    'status' => 'approved',
                                    'approved_by' => $approver->id,
                                    'approved_at' => now(),
                                ]);
                                $count++;
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title("{$count} Attendance Records Approved")
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\BulkAction::make('reject_selected')
                        ->label('✗ Reject Selected')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->modalHeading('Reject Selected Attendance Records')
                        ->modalDescription('Are you sure you want to reject these attendance records?')
                        ->form([
                            Forms\Components\Textarea::make('rejection_reason')
                                ->label('Rejection Reason')
                                ->required()
                                ->placeholder('Provide a reason for rejecting these records')
                                ->maxLength(500),
                        ])
                        ->action(function ($records, array $data) {
                            $approver = Auth::user();
                            $count = 0;
                            
                            foreach ($records as $record) {
                                // Skip own records
                                if ($record->user_id === $approver->id) {
                                    continue;
                                }
                                
                                $record->update([
                                    'status' => 'rejected',
                                    'approval_notes' => $data['rejection_reason'] ?? null,
                                    'approved_by' => $approver->id,
                                    'approved_at' => now(),
                                ]);
                                $count++;
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title("{$count} Attendance Records Rejected")
                                ->warning()
                                ->send();
                        }),
                ]),
            ])
            ->paginated([10, 25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClockInApprovals::route('/'),
        ];
    }
}
