<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Services\AppNotificationService;
use Illuminate\Console\Command;

class CheckFleetComplianceAlerts extends Command
{
    protected $signature = 'fleet:check-alerts';

    protected $description = 'Scan fleet vehicles for service due and road tax expiration and dispatch database notifications to management';

    public function handle(): int
    {
        $vehicles = Vehicle::query()
            ->where('is_active', true)
            ->get();

        $alertCount = 0;

        foreach ($vehicles as $vehicle) {
            $alerts = [];

            // 1. Check Service Mileage Status
            if ($vehicle->isServiceOverdue()) {
                $alerts[] = 'Service is OVERDUE by ' . number_format((float) $vehicle->kmOverdue()) . ' KM';
            } elseif ($vehicle->isServiceDueSoon(500)) {
                $alerts[] = 'Service DUE SOON in ' . number_format((float) $vehicle->serviceMileageDifference()) . ' KM';
            }

            // 2. Check Road Tax Expiry Status (14 days alert threshold)
            if ($vehicle->isRoadTaxExpired()) {
                $days = abs((int) $vehicle->daysUntilRoadTaxExpiry());
                $alerts[] = "Road tax EXPIRED {$days} day(s) ago ({$vehicle->road_tax_expiry?->format('d/m/Y')})";
            } elseif ($vehicle->isRoadTaxExpiringSoon(14)) {
                $days = (int) $vehicle->daysUntilRoadTaxExpiry();
                $alerts[] = "Road tax EXPIRING in {$days} day(s) ({$vehicle->road_tax_expiry?->format('d/m/Y')})";
            }

            if (! empty($alerts)) {
                AppNotificationService::notifyFleetComplianceAlert($vehicle, $alerts);
                $this->warn("Alert sent for {$vehicle->numberplate}: " . implode('; ', $alerts));
                $alertCount++;
            }
        }

        $this->info("Fleet compliance scan complete. Dispatched alerts for {$alertCount} vehicle(s).");

        return self::SUCCESS;
    }
}

