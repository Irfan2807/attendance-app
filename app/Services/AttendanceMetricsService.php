<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceMetricsService
{
    public static function overtimeThresholdMinutes(): int
    {
        return (int) config('attendance.standard_workday_hours', 8) * 60;
    }

    public static function workedMinutes(Attendance $attendance, ?Carbon $reference = null): int
    {
        if (!$attendance->clock_in_time) {
            return 0;
        }

        $endTime = $attendance->clock_out_time ?? ($reference ? $reference->copy() : now());

        if ($endTime->lte($attendance->clock_in_time)) {
            return 0;
        }

        return $attendance->clock_in_time->diffInMinutes($endTime);
    }

    public static function overtimeMinutes(Attendance $attendance, ?Carbon $reference = null): int
    {
        if (!$attendance->clock_in_time) {
            return 0;
        }

        $endTime = $attendance->clock_out_time ?? ($reference ? $reference->copy() : now());

        if ($endTime->lte($attendance->clock_in_time)) {
            return 0;
        }

        $otStart = $attendance->clock_in_time->copy()->addMinutes(self::overtimeThresholdMinutes());

        if ($endTime->lte($otStart)) {
            return 0;
        }

        return $otStart->diffInMinutes($endTime);
    }

    public static function formatMinutes(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0m';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours === 0) {
            return $remainingMinutes . 'm';
        }

        if ($remainingMinutes === 0) {
            return $hours . 'h';
        }

        return $hours . 'h ' . $remainingMinutes . 'm';
    }
}