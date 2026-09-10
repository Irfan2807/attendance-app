<?php

namespace App\Filament\Staff\Resources;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Enums\Role;
use App\Filament\Staff\Resources\StaffLeaveApprovalResource\Pages;
use App\Models\LeaveRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StaffLeaveApprovalResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Leave & MC Approvals';
    protected static ?string $navigationGroup = 'Management';
    protected static ?string $slug = 'leave-approvals';
    protected static ?string $modelLabel = 'Leave Approval';

    public static function canViewAny(): bool
    {
        return Auth::user()?->isManagerOrAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // Approvals cannot be created manually
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()
            ->where('status', LeaveStatus::Pending->value)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        // Governance rules:
        // 1. Managers only approve their assigned subordinates (or unassigned staff). Peer-manager approvals blocked.
        // 2. HR Executive and Director can approve both Managers and Staff company-wide.
        // 3. Cannot approve own requests.
        $query = parent::getEloquentQuery()
            ->with(['user', 'actionedBy'])
            ->where('user_id', '!=', Auth::user()?->id);

        if (Auth::user()?->isManager()) {
            $managerId = Auth::user()?->id;
            $query->whereHas('user', function ($q) use ($managerId) {
                $q->where('role', Role::Staff->value)
                  ->where(function ($sub) use ($managerId) {
                      $sub->where('manager_id', $managerId)
                          ->orWhereNull('manager_id');
                  });
            });
        }

        return $query
            ->orderByRaw("CASE WHEN status = 'pending' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Applicant Information')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('Staff Member')
                            ->disabled(),
                        Forms\Components\TextInput::make('user.phone')
                            ->label('Contact Phone')
                            ->disabled(),
                        Forms\Components\TextInput::make('leave_type')
                            ->label('Leave Type')
                            ->formatStateUsing(fn ($record) => $record?->leave_type?->label() ?? '')
                            ->disabled(),
                        Forms\Components\TextInput::make('days_count')
                            ->label('Total Days')
                            ->suffix('day(s)')
                            ->disabled(),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('From')
                            ->disabled(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('To')
                            ->disabled(),
                        Forms\Components\Textarea::make('reason')
                            ->label('Staff Remarks')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Uploaded Supporting Evidence')
                    ->schema([
                        Forms\Components\FileUpload::make('attachment_path')
                            ->label('Medical Certificate / Evidence')
                            ->disk('public')
                            ->disabled()
                            ->openable()
                            ->downloadable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Staff Name')
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
                    ->trueColor('primary')
                    ->tooltip(fn ($record) => $record->attachment_path ? 'Attachment available' : 'No attachment'),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Remarks')
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->reason)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Applied')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(LeaveStatus::options())
                    ->default(LeaveStatus::Pending->value),

                Tables\Filters\SelectFilter::make('leave_type')
                    ->label('Leave Type')
                    ->options(LeaveType::options()),
            ])
            ->actions([
                // 1. View Evidence Modal / Document Link
                Tables\Actions\Action::make('view_attachment')
                    ->label('View MC')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (LeaveRequest $record) => !empty($record->attachment_path))
                    ->url(fn (LeaveRequest $record) => $record->attachment_url, shouldOpenInNewTab: true),

                // 2. Approve Action
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (LeaveRequest $record) => $record->isPending())
                    ->requiresConfirmation()
                    ->modalHeading('Approve Leave Application')
                    ->modalDescription(fn (LeaveRequest $record) => "Approve {$record->days_count} day(s) {$record->leave_type->label()} for {$record->user->name}?")
                    ->action(function (LeaveRequest $record) {
                        $record->update([
                            'status' => LeaveStatus::Approved,
                            'actioned_by' => Auth::user()?->id,
                            'actioned_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Leave Approved')
                            ->body("Leave request for {$record->user->name} has been approved.")
                            ->success()
                            ->send();
                    }),

                // 3. Reject Action
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (LeaveRequest $record) => $record->isPending())
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
                            ->body("Leave request for {$record->user->name} was marked rejected.")
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
            'index' => Pages\ListStaffLeaveApprovals::route('/'),
        ];
    }
}
