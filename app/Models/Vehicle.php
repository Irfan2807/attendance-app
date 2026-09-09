<?php

namespace App\Models;

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
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
}
