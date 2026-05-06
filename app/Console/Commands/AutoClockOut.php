<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\AttendanceInfraction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoClockOut extends Command
{
    protected $signature = 'attendance:auto-clock-out';

    protected $description = 'Auto clock-out shifts that have exceeded the maximum shift length and create infractions';

    public function handle(): void
    {
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
                    'infraction_type' => 'auto_clock_out_' . $attendance->clock_in_time->diffInHours($now),
                    'auto_clock_out_time' => $now,
                    'notes' => 'Auto clocked out after exceeding max shift length',
                ]);

                // Increment user's incomplete clock-out counter
                User::where('id', $attendance->user_id)
                    ->increment('incomplete_clock_out_count');
            });
        }
    }
}
