<?php

namespace App\Services;

use Carbon\Carbon;

class AttendanceWindowService
{
    public static function operationalDayStart(?Carbon $reference = null, string $shiftType = 'day'): Carbon
    {
        $reference = $reference ? $reference->copy() : now();
        $cutoffHour = $shiftType === 'night'
            ? (int) config('attendance.night_shift_starts_at', 17)
            : (int) config('attendance.day_shift_starts_at', 8);

        $start = $reference->copy()->startOfDay()->addHours($cutoffHour);

        if ($reference->lt($start)) {
            $start->subDay();
        }

        return $start;
    }

    public static function operationalDayEnd(?Carbon $reference = null, string $shiftType = 'day'): Carbon
    {
        return self::operationalDayStart($reference, $shiftType)->copy()->addDay();
    }

    public static function operationalDayRange(?Carbon $reference = null, string $shiftType = 'day'): array
    {
        $start = self::operationalDayStart($reference, $shiftType);

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
