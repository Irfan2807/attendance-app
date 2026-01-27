<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AttendanceInfraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'infraction_type',
        'auto_clock_out_time',
        'notes',
    ];

    protected $casts = [
        'auto_clock_out_time' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Get the count of warnings for a user in the current month
     */
    public static function getMonthlyWarningCount(int $userId): int
    {
        return static::where('user_id', $userId)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
    }

    /**
     * Check if user needs manager escalation (3+ warnings in current month)
     */
    public static function needsManagerEscalation(int $userId): bool
    {
        return static::getMonthlyWarningCount($userId) >= 3;
    }
}
