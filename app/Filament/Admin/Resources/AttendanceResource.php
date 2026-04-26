<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use App\Services\AttendanceMetricsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Attendance Logs';

    public static function getEloquentQuery(): Builder
    {
        // Eager load relationships to prevent N+1 queries
        return parent::getEloquentQuery()->with(['user', 'approver']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Employee'),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending Approval',
                        'temporary' => 'Temporary (Clocked Out)',
                        'approved' => 'Approved',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required(),

                Forms\Components\TextInput::make('site_name')
                    ->label('Site / Location')
                    ->maxLength(255),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->numeric(),
                        Forms\Components\TextInput::make('longitude')
                            ->numeric(),
                    ]),

                Forms\Components\DateTimePicker::make('clock_in_time')
                    ->label('Clock In Time')
                    ->default(now())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'approved',
                        'info' => 'completed',
                        'warning' => ['pending', 'temporary'],
                        'danger' => 'rejected',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('site_name')
                    ->label('Location')
                    ->limit(30),

                Tables\Columns\TextColumn::make('clock_in_time')
                    ->label('Clock In')
                    ->dateTime('d M Y h:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('clock_out_time')
                    ->label('Clock Out')
                    ->dateTime('d M Y h:i A')
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending Approval',
                        'temporary' => 'Temporary (Clocked Out)',
                        'approved' => 'Approved',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ]),

                Filter::make('today')
                    ->label('Today Only')
                    ->query(fn (Builder $query) => $query->whereDate('clock_in_time', today())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (Attendance $record) => $record->update(['status' => 'approved']))
                    ->visible(fn (Attendance $record) => in_array($record->status, ['pending', 'temporary'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
        ];
    }
}
