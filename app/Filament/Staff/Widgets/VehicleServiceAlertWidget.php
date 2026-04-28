<?php

namespace App\Filament\Staff\Widgets;

use App\Models\Vehicle;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;

#[Lazy]
class VehicleServiceAlertWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Only show to managers
        return Auth::user() && Auth::user()->role === 2;
    }

    protected function getTableHeading(): ?string
    {
        return '🚨 Vehicle Service Alerts';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vehicle::query()
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereRaw('(next_service_mileage - current_mileage) <= 500')
                          ->orWhereRaw('current_mileage >= next_service_mileage');
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('numberplate')
                    ->label('Number Plate')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Vehicle'),

                Tables\Columns\TextColumn::make('current_mileage')
                    ->label('Current')
                    ->formatStateUsing(fn($state) => number_format($state) . ' KM'),

                Tables\Columns\TextColumn::make('next_service_mileage')
                    ->label('Service Due')
                    ->formatStateUsing(fn($state) => number_format($state) . ' KM'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn($record) => $record->isServiceOverdue() ? 'OVERDUE' : 'DUE SOON')
                    ->colors([
                        'danger' => 'OVERDUE',
                        'warning' => 'DUE SOON',
                    ]),

                Tables\Columns\TextColumn::make('km_remaining')
                    ->label('KM Remaining')
                    ->getStateUsing(fn($record) => $record->kmUntilService())
                    ->formatStateUsing(fn($state) => $state <= 0 ? 'Overdue by ' . number_format(abs($state)) . ' KM' : number_format($state) . ' KM')
                    ->color(fn($state) => $state <= 0 ? 'danger' : 'warning')
                    ->weight('bold'),
            ])
            ->actions([
                Tables\Actions\Action::make('update_service')
                    ->label('Update Service')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('next_service_mileage')
                            ->label('Next Service at (KM)')
                            ->required()
                            ->numeric()
                            ->minValue(fn($record) => $record->current_mileage)
                            ->default(fn($record) => $record->current_mileage + 10000)
                            ->suffix('KM')
                            ->helperText('Typically +10,000 KM from current mileage'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'next_service_mileage' => $data['next_service_mileage'],
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Service Updated')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
