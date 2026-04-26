<?php

return [
    // Default day shift starts at 08:00 and follows a flexible operational window.
    'day_shift_starts_at' => env('ATTENDANCE_DAY_START_HOUR', 8),

    // Night shift starts at 17:00 by default.
    'night_shift_starts_at' => env('ATTENDANCE_NIGHT_SHIFT_START_HOUR', 17),

    // Hard limit for a single open shift before it is auto-closed.
    'max_shift_hours' => env('ATTENDANCE_MAX_SHIFT_HOURS', 16),

    // Standard workday length used to trigger overtime.
    'standard_workday_hours' => env('ATTENDANCE_STANDARD_WORKDAY_HOURS', 8),

    // Legacy shift window values retained for existing operational-day logic.
    'day_shift_ot_starts_at' => env('ATTENDANCE_DAY_OT_START_HOUR', 17),
    'night_shift_standard_hours' => env('ATTENDANCE_NIGHT_STANDARD_HOURS', 8),
];
