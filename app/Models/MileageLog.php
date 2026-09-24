<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MileageLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'start_mileage',
        'end_mileage',
        'distance_km',
        'destination',
        'purpose',
        'site_id',
        'mileage_reading',
        'recorded_at',
        'notes',
    ];

    protected $casts = [
        'start_mileage' => 'integer',
        'end_mileage' => 'integer',
        'distance_km' => 'integer',
        'recorded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (MileageLog $log) {
            if (empty($log->site_id)) {
                $log->site_id = null;
            }

            // Keep mileage_reading and end_mileage in sync for backward compatibility
            if ($log->end_mileage !== null && $log->mileage_reading === null) {
                $log->mileage_reading = $log->end_mileage;
            } elseif ($log->mileage_reading !== null && $log->end_mileage === null) {
                $log->end_mileage = $log->mileage_reading;
            }

            // Auto-compute distance_km
            if ($log->start_mileage !== null && $log->end_mileage !== null) {
                $log->distance_km = max(0, $log->end_mileage - $log->start_mileage);
            }
        });
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the formatted destination (registered Site name or custom destination string).
     */
    public function getResolvedDestinationAttribute(): string
    {
        return $this->site?->name ?? $this->destination ?? 'Not Specified';
    }
}
