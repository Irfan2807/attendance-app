<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function mileageLogs(): HasMany
    {
        return $this->hasMany(MileageLog::class);
    }

    // Check if service is due soon (within 500km)
    public function isServiceDueSoon(): bool
    {
        return ($this->next_service_mileage - $this->current_mileage) <= 500;
    }

    // Check if service is overdue
    public function isServiceOverdue(): bool
    {
        return $this->current_mileage >= $this->next_service_mileage;
    }

    // Get remaining KM until service
    public function kmUntilService(): int
    {
        return max(0, $this->next_service_mileage - $this->current_mileage);
    }
}
