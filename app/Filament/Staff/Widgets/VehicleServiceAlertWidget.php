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
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Only show to managers and admins
        return Auth::user() && Auth::user()->isManagerOrAdmin();
    }

    protected function getTableHeading(): ?string
    {
        return '🚨 Fleet Compliance Alerts (Service & Road Tax)';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vehicle::query()
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereRaw('(next_service_mileage - current_mileage) <= 500')
                          ->orWhereRaw('current_mileage >= next_service_mileage')
                          ->orWhere(function ($rq) {
                              $rq->whereNotNull('road_tax_expiry')
                                 ->where('road_tax_expiry', '<=', now()->addDays(30)->toDateString());
                          });
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
                    ->formatStateUsing(fn($state) => number_format((float) $state) . ' KM'),

                Tables\Columns\TextColumn::make('next_service_mileage')
                    ->label('Service Due')
                    ->formatStateUsing(fn($state) => number_format((float) $state) . ' KM'),

                Tables\Columns\TextColumn::make('road_tax_expiry')
                    ->label('Road Tax')
                    ->date('d/m/Y')
                    ->placeholder('Not Set')
                    ->description(fn ($record) => $record->road_tax_expiry ? $record->roadTaxStatusLabel() : null)
                    ->badge()
                    ->color(fn ($record) => $record->roadTaxBadgeColor()),

                Tables\Columns\TextColumn::make('alert_types')
                    ->label('Alerts')
                    ->getStateUsing(function ($record): array {
                        $alerts = [];
                        if ($record->isServiceOverdue()) {
                            $alerts[] = 'SERVICE OVERDUE';
                        } elseif ($record->isServiceDueSoon()) {
                            $alerts[] = 'SERVICE DUE';
                        }

                        if ($record->isRoadTaxExpired()) {
                            $alerts[] = 'ROAD TAX EXPIRED';
                        } elseif ($record->isRoadTaxExpiringSoon()) {
                            $alerts[] = 'ROAD TAX DUE';
                        }

                        return $alerts ?: ['OK'];
                    })
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'EXPIRED') || str_contains($state, 'OVERDUE') => 'danger',
                        str_contains($state, 'DUE') => 'warning',
                        default => 'success',
                    }),
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

                Tables\Actions\Action::make('renew_road_tax')
                    ->label('Renew Road Tax')
                    ->icon('heroicon-o-document-check')
                    ->color('info')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('road_tax_expiry')
                            ->label('New Expiry Date')
                            ->required()
                            ->default(fn ($record) => $record->road_tax_expiry ? $record->road_tax_expiry->copy()->addYear() : now()->addYear()),

                        \Filament\Forms\Components\TextInput::make('road_tax_amount')
                            ->label('Renewal Cost (RM)')
                            ->numeric()
                            ->prefix('RM'),

                        \Filament\Forms\Components\FileUpload::make('road_tax_document')
                            ->label('Upload Digital Road Tax / Grant')
                            ->directory('road-taxes')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp']),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update($data);

                        \Filament\Notifications\Notification::make()
                            ->title('Road Tax Renewed')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
