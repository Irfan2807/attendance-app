<?php

namespace App\Filament\Staff\Resources;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Filament\Staff\Resources\StaffLeaveRequestResource\Pages;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StaffLeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Apply / My Leave';
    protected static ?string $navigationGroup = 'My Shift';
    protected static ?string $slug = 'my-leaves';
    protected static ?string $modelLabel = 'Leave Request';

    public static function canViewAny(): bool
    {
        return Auth::check();
    }

    public static function canCreate(): bool
    {
        return Auth::check();
    }

    public static function canEdit($record): bool
    {
        // Only allow editing if request is still pending and belongs to user
        return $record->user_id === Auth::user()?->id && $record->isPending();
    }

    public static function canDelete($record): bool
    {
        // Only allow deletion/cancellation if still pending
        return $record->user_id === Auth::user()?->id && $record->isPending();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::user()?->id)
            ->with(['actionedBy'])
            ->orderByDesc('created_at');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Leave Information')
                    ->description('Select the type of leave and the requested dates.')
                    ->schema([
                        Forms\Components\Select::make('leave_type')
                            ->label('Leave Type')
                            ->options(LeaveType::options())
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText(function (Forms\Get $get) {
                                $type = $get('leave_type');
                                if (! $type) {
                                    return null;
                                }
                                $user = Auth::user();
                                $remaining = $user?->remainingLeave($type);
                                $quota = $user?->leaveQuota($type);
                                if ($remaining !== null) {
                                    return "Available balance for " . now()->year . ": {$remaining} / {$quota} days remaining.";
                                }
                                if ($type === LeaveType::UnpaidLeave->value) {
                                    return "Unpaid leave does not consume any quota.";
                                }
                                return null;
                            }),

                        Forms\Components\TextInput::make('days_count')
                            ->label('Total Days')
                            ->numeric()
                            ->default(1.0)
                            ->step(0.5)
                            ->minValue(0.5)
                            ->required()
                            ->rules([
                                fn (Forms\Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $type = $get('leave_type');
                                    if (! $type) {
                                        return;
                                    }
                                    $user = Auth::user();
                                    $remaining = $user?->remainingLeave($type);
                                    if ($remaining !== null && (float) $value > $remaining) {
                                        $label = LeaveType::tryFrom($type)?->label() ?? 'Leave';
                                        $fail("Insufficient {$label} balance. You only have {$remaining} day(s) remaining for " . now()->year . ". Please adjust your dates or apply for Unpaid Leave.");
                                    }
                                },
                            ])
                            ->helperText('e.g. 1.0 for full day, 0.5 for half day.'),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                $start = $get('start_date');
                                $end = $get('end_date');
                                if ($start && $end) {
                                    $days = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
                                    $set('days_count', max(1, $days));
                                }
                            }),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->required()
                            ->native(false)
                            ->minDate(fn (Forms\Get $get) => $get('start_date') ?? null)
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                $start = $get('start_date');
                                $end = $get('end_date');
                                if ($start && $end) {
                                    $days = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
                                    $set('days_count', max(1, $days));
                                }
                            }),

                        Forms\Components\Textarea::make('reason')
                            ->label('Reason / Remarks')
                            ->rows(3)
                            ->placeholder('Brief reason for leave request')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Supporting Evidence (Medical Certificate / Proof)')
                    ->description('Upload clinic receipt, doctor note, or supporting documents.')
                    ->schema([
                        Forms\Components\FileUpload::make('attachment_path')
                            ->label('Medical Certificate / Evidence')
                            ->disk('public')
                            ->directory('leave-attachments')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                            ->maxSize(5120)
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->required(fn (Forms\Get $get) => in_array($get('leave_type'), [LeaveType::MedicalLeave->value, LeaveType::Hospitalization->value]))
                            ->helperText('Required for Medical Leave (MC) and Hospitalization. Max 5MB (JPG, PNG, or PDF).')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('leave_type')
                    ->label('Leave Type')
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
                    ->suffix(' day(s)'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof LeaveStatus ? $state->label() : (LeaveStatus::tryFrom($state)?->label() ?? ucfirst($state)))
                    ->color(fn ($state) => $state instanceof LeaveStatus ? $state->color() : (LeaveStatus::tryFrom($state)?->color() ?? 'gray')),

                Tables\Columns\IconColumn::make('attachment_path')
                    ->label('Proof / MC')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('primary'),

                Tables\Columns\TextColumn::make('actionedBy.name')
                    ->label('Reviewed By')
                    ->placeholder('Pending')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->reason)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('rejection_reason')
                    ->label('Rejection Reason')
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->rejection_reason)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Cancel')
                    ->visible(fn ($record) => $record->isPending()),
            ])
            ->bulkActions([]);
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Staff\Widgets\StaffLeaveBalanceWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaffLeaveRequests::route('/'),
            'create' => Pages\CreateStaffLeaveRequest::route('/create'),
            'view' => Pages\ViewStaffLeaveRequest::route('/{record}'),
        ];
    }
}
