<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $fillable = [
        'numberplate',
        'name',
        'current_mileage',
        'next_service_mileage',
        'is_active',
        'notes',
        'road_tax_expiry',
        'road_tax_amount',
        'road_tax_document',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'road_tax_expiry' => 'date',
        'road_tax_amount' => 'decimal:2',
    ];

    public function mileageLogs(): HasMany
    {
        return $this->hasMany(MileageLog::class);
    }

    // Check if service is due soon (within 500km and not yet overdue)
    public function isServiceDueSoon(): bool
    {
        $remaining = $this->next_service_mileage - $this->current_mileage;

        return $remaining > 0 && $remaining <= 500;
    }

    // Check if service is overdue
    public function isServiceOverdue(): bool
    {
        return $this->current_mileage >= $this->next_service_mileage;
    }

    // Get remaining KM until service (clamped to 0)
    public function kmUntilService(): int
    {
        return max(0, $this->next_service_mileage - $this->current_mileage);
    }

    // Get net KM difference: positive = remaining until service, negative = overdue
    public function serviceMileageDifference(): int
    {
        return $this->next_service_mileage - $this->current_mileage;
    }

    // Get overdue KM: 0 if not overdue, positive if overdue
    public function kmOverdue(): int
    {
        return max(0, $this->current_mileage - $this->next_service_mileage);
    }

    // Days until road tax expiry: positive = days left, negative = overdue, null = not set
    public function daysUntilRoadTaxExpiry(?Carbon $referenceDate = null): ?int
    {
        if (! $this->road_tax_expiry) {
            return null;
        }

        $ref = ($referenceDate ? $referenceDate->copy() : Carbon::now())->startOfDay();
        $expiry = $this->road_tax_expiry->copy()->startOfDay();

        return (int) $ref->diffInDays($expiry, false);
    }

    // Check if road tax is already expired
    public function isRoadTaxExpired(?Carbon $referenceDate = null): bool
    {
        if (! $this->road_tax_expiry) {
            return false;
        }

        $days = $this->daysUntilRoadTaxExpiry($referenceDate);

        return $days !== null && $days < 0;
    }

    // Check if road tax is expiring soon (within 30 days)
    public function isRoadTaxExpiringSoon(int $days = 30, ?Carbon $referenceDate = null): bool
    {
        if (! $this->road_tax_expiry) {
            return false;
        }

        $remaining = $this->daysUntilRoadTaxExpiry($referenceDate);

        return $remaining !== null && $remaining >= 0 && $remaining <= $days;
    }

    // Get unified road tax status key
    public function roadTaxStatus(?Carbon $referenceDate = null): string
    {
        if (! $this->road_tax_expiry) {
            return 'not_set';
        }

        if ($this->isRoadTaxExpired($referenceDate)) {
            return 'expired';
        }

        if ($this->isRoadTaxExpiringSoon(30, $referenceDate)) {
            return 'expiring_soon';
        }

        return 'valid';
    }

    // Get Filament badge color for road tax status
    public function roadTaxBadgeColor(?Carbon $referenceDate = null): string
    {
        return match ($this->roadTaxStatus($referenceDate)) {
            'expired' => 'danger',
            'expiring_soon' => 'warning',
            'valid' => 'success',
            default => 'gray',
        };
    }

    // Get human-readable road tax status label
    public function roadTaxStatusLabel(?Carbon $referenceDate = null): string
    {
        $days = $this->daysUntilRoadTaxExpiry($referenceDate);

        if ($days === null) {
            return 'Not Set';
        }

        if ($days < 0) {
            return 'Expired ' . abs($days) . ' days ago';
        }

        if ($days === 0) {
            return 'Expires Today';
        }

        if ($days <= 30) {
            return 'Expires in ' . $days . ' days';
        }

        return $days . ' days remaining';
    }
}
