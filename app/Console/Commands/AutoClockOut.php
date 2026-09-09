<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\AttendanceInfraction;
use App\Models\User;
use App\Services\AttendanceWindowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoClockOut extends Command
{
    protected $signature = 'attendance:auto-clock-out';

    protected $description = 'Auto clock-out shifts that have exceeded the maximum shift length and create infractions';

    public function handle(): void
    {
        $thresholdHours = AttendanceWindowService::maxShiftHours();
        $now = now();

        $longRunning = Attendance::whereNull('clock_out_time')
            ->where('clock_in_time', '<=', $now->copy()->subHours($thresholdHours))
            ->get();

        foreach ($longRunning as $attendance) {
            DB::transaction(function () use ($attendance, $now) {
                $existingNotes = trim((string) $attendance->verification_notes);
                $autoNote = 'Auto-closed stale shift after max shift duration';
                $updatedNotes = $existingNotes !== '' ? $existingNotes . ' | ' . $autoNote : $autoNote;

                // Auto clock-out
                $attendance->update([
                    'clock_out_time' => $now,
                    'status' => 'temporary', // requires approval
                    'verification_notes' => $updatedNotes,
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
