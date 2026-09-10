<?php

namespace App\Filament\Admin\Resources;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Filament\Admin\Resources\LeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Leave Requests';
    protected static ?string $navigationGroup = 'Workforce';
    protected static ?string $slug = 'leave-requests';
    protected static ?string $modelLabel = 'Leave Request';

    public static function canViewAny(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', LeaveStatus::Pending->value)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'actionedBy'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Applicant Details')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('Employee')
                            ->disabled(),
                        Forms\Components\TextInput::make('user.phone')
                            ->label('Phone')
                            ->disabled(),
                        Forms\Components\TextInput::make('leave_type')
                            ->label('Leave Type')
                            ->formatStateUsing(fn ($record) => $record?->leave_type?->label() ?? '')
                            ->disabled(),
                        Forms\Components\TextInput::make('days_count')
                            ->label('Total Days')
                            ->disabled(),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->disabled(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->disabled(),
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Applicant Leave Balances')
                    ->description('Current year quota usage for this employee.')
                    ->schema([
                        Forms\Components\Placeholder::make('annual_leave_balance')
                            ->label('Annual Leave Balance')
                            ->content(function (?LeaveRequest $record): string {
                                if (! $record?->user) {
                                    return '—';
                                }
                                $remaining = $record->user->remainingLeave(LeaveType::AnnualLeave, $record->start_date?->year);
                                $quota = $record->user->leaveQuota(LeaveType::AnnualLeave);
                                $taken = $record->user->approvedLeaveTaken(LeaveType::AnnualLeave, $record->start_date?->year);
                                return "{$remaining} / {$quota} days remaining ({$taken} days used)";
                            }),

                        Forms\Components\Placeholder::make('medical_leave_balance')
                            ->label('Medical Leave (MC) Balance')
                            ->content(function (?LeaveRequest $record): string {
                                if (! $record?->user) {
                                    return '—';
                                }
                                $remaining = $record->user->remainingLeave(LeaveType::MedicalLeave, $record->start_date?->year);
                                $quota = $record->user->leaveQuota(LeaveType::MedicalLeave);
                                $taken = $record->user->approvedLeaveTaken(LeaveType::MedicalLeave, $record->start_date?->year);
                                return "{$remaining} / {$quota} days remaining ({$taken} days used)";
                            }),
                    ])->columns(2),

                Forms\Components\Section::make('Attached Supporting Evidence')
                    ->schema([
                        Forms\Components\FileUpload::make('attachment_path')
                            ->label('MC / Proof')
                            ->disk('public')
                            ->disabled()
                            ->openable()
                            ->downloadable()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Review & Governance')
                    ->schema([
                        Forms\Components\TextInput::make('status')
                            ->label('Status')
                            ->formatStateUsing(fn ($record) => $record?->status?->label() ?? '')
                            ->disabled(),
                        Forms\Components\TextInput::make('actionedBy.name')
                            ->label('Actioned By')
                            ->placeholder('Pending')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('actioned_at')
                            ->label('Actioned At')
                            ->disabled(),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('leave_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof LeaveType ? $state->label() : (LeaveType::tryFrom($state)?->label() ?? ucfirst($state)))
                    ->color(fn ($state) => $state instanceof LeaveType ? $state->color() : (LeaveType::tryFrom($state)?->color() ?? 'gray')),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('From')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('To')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('days_count')
                    ->label('Days')
                    ->suffix(' d'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof LeaveStatus ? $state->label() : (LeaveStatus::tryFrom($state)?->label() ?? ucfirst($state)))
                    ->color(fn ($state) => $state instanceof LeaveStatus ? $state->color() : (LeaveStatus::tryFrom($state)?->color() ?? 'gray')),

                Tables\Columns\IconColumn::make('attachment_path')
                    ->label('MC / Proof')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('primary'),

                Tables\Columns\TextColumn::make('actionedBy.name')
                    ->label('Approver')
                    ->placeholder('Pending')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Applied')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(LeaveStatus::options()),
                Tables\Filters\SelectFilter::make('leave_type')
                    ->options(LeaveType::options()),
            ])
            ->actions([
                Tables\Actions\Action::make('view_attachment')
                    ->label('View MC')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (LeaveRequest $record) => !empty($record->attachment_path))
                    ->url(fn (LeaveRequest $record) => $record->attachment_url, shouldOpenInNewTab: true),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (LeaveRequest $record) => $record->isPending() && $record->user_id !== Auth::user()?->id)
                    ->requiresConfirmation()
                    ->modalHeading('Approve Leave Request')
                    ->modalDescription(fn (LeaveRequest $record) => "Approve {$record->days_count} day(s) {$record->leave_type->label()} for {$record->user?->name}?")
                    ->action(function (LeaveRequest $record) {
                        $record->update([
                            'status' => LeaveStatus::Approved,
                            'actioned_by' => Auth::user()?->id,
                            'actioned_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Leave Approved')
                            ->body("Leave request for {$record->user?->name} has been approved.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (LeaveRequest $record) => $record->isPending() && $record->user_id !== Auth::user()?->id)
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->placeholder('State reason (e.g., inadequate staffing, incomplete documentation)'),
                    ])
                    ->action(function (LeaveRequest $record, array $data) {
                        $record->update([
                            'status' => LeaveStatus::Rejected,
                            'actioned_by' => Auth::user()?->id,
                            'actioned_at' => now(),
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()
                            ->title('Leave Rejected')
                            ->body("Leave request for {$record->user?->name} was marked rejected.")
                            ->danger()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaveRequests::route('/'),
        ];
    }
}

