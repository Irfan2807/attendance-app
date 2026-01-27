<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\AttendanceInfraction;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto clock-out safety net: enforce max 10 hours per shift and create warnings
Schedule::call(function () {
    // Find active shifts longer than 9 or 10 hours
    $thresholdHours = 10;
    $now = now();

    $longRunning = Attendance::whereNull('clock_out_time')
        ->where('clock_in_time', '<=', $now->copy()->subHours($thresholdHours))
        ->get();

    foreach ($longRunning as $attendance) {
        DB::transaction(function () use ($attendance, $now) {
            // Auto clock-out
            $attendance->update([
                'clock_out_time' => $now,
                'status' => 'temporary', // requires approval
            ]);

            // Create infraction
            AttendanceInfraction::create([
                'user_id' => $attendance->user_id,
                'attendance_id' => $attendance->id,
                'infraction_type' => 'auto_clock_out_'.($now->diffInHours($attendance->clock_in_time)),
                'auto_clock_out_time' => $now,
                'notes' => 'Auto clocked out after exceeding max shift length',
            ]);

            // Increment user's incomplete clock-out counter
            User::where('id', $attendance->user_id)
                ->increment('incomplete_clock_out_count');
        });
    }
})->everyTenMinutes()->name('attendance:auto-clock-out');
