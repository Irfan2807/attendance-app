<?php

namespace App\Services;

use Carbon\Carbon;

class AttendanceWindowService
{
    public static function operationalDayStart(?Carbon $reference = null): Carbon
    {
        $reference = $reference ? $reference->copy() : now();
        $cutoffHour = (int) config('attendance.day_shift_starts_at', 8);

        $start = $reference->copy()->startOfDay()->addHours($cutoffHour);

        if ($reference->lt($start)) {
            $start->subDay();
        }

        return $start;
    }

    public static function operationalDayEnd(?Carbon $reference = null): Carbon
    {
        return self::operationalDayStart($reference)->copy()->addDay();
    }

    public static function operationalDayRange(?Carbon $reference = null): array
    {
        $start = self::operationalDayStart($reference);

        return [$start, $start->copy()->addDay()];
    }

    public static function maxShiftHours(): int
    {
        return (int) config('attendance.max_shift_hours', 16);
    }

    public static function isStaleShift(Carbon $clockInTime, ?Carbon $reference = null): bool
    {
        $reference = $reference ? $reference->copy() : now();

        return $clockInTime->diffInHours($reference) >= self::maxShiftHours();
    }
}
