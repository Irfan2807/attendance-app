<?php

namespace App\Filament\Staff\Widgets;

use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;

#[Lazy]
class ClockInDetailsWidget extends Widget
{
    protected static string $view = 'filament.staff.widgets.clock-in-details-widget';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = [
        'default' => 1,
        'lg' => 1,
    ];

    public array $stats = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function hydrate(): void
    {
        $this->loadStats();
    }

    private function loadStats(): void
    {
        $user = Auth::user();
        $now = Carbon::now();

        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        $monthStart = $now->copy()->startOfMonth();

        $todayAttendances = Attendance::where('user_id', $user->id)
            ->whereBetween('clock_in_time', [$todayStart, $todayEnd])
            ->get();

        $weekAttendances = Attendance::where('user_id', $user->id)
            ->whereBetween('clock_in_time', [$weekStart, $weekEnd])
            ->get();

        $monthAttendances = Attendance::where('user_id', $user->id)
            ->whereBetween('clock_in_time', [$monthStart, $now])
            ->get();

        $activeShift = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out_time')
            ->latest('clock_in_time')
            ->first();

        $lastShift = Attendance::where('user_id', $user->id)
            ->latest('clock_in_time')
            ->first();

        $pendingCount = Attendance::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'temporary'])
            ->count();

        $completedThisMonth = Attendance::where('user_id', $user->id)
            ->whereBetween('clock_in_time', [$monthStart, $now])
            ->whereNotNull('clock_out_time')
            ->count();

        $this->stats = [
            'todayMinutes' => $todayAttendances->sum(fn ($attendance) => $this->durationMinutes($attendance)),
            'weekMinutes' => $weekAttendances->sum(fn ($attendance) => $this->durationMinutes($attendance)),
            'monthMinutes' => $monthAttendances->sum(fn ($attendance) => $this->durationMinutes($attendance)),
            'activeShift' => $activeShift,
            'activeMinutes' => $activeShift ? $activeShift->clock_in_time->diffInMinutes($now) : 0,
            'lastShift' => $lastShift,
            'lastShiftMinutes' => $lastShift ? $this->durationMinutes($lastShift) : 0,
            'pendingCount' => $pendingCount,
            'completedThisMonth' => $completedThisMonth,
        ];
    }

    private function durationMinutes(Attendance $attendance): int
    {
        $endTime = $attendance->clock_out_time ?? Carbon::now();

        return $attendance->clock_in_time->diffInMinutes($endTime);
    }

    public function formatMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours <= 0) {
            return $remainingMinutes . 'm';
        }

        if ($remainingMinutes === 0) {
            return $hours . 'h';
        }

        return $hours . 'h ' . $remainingMinutes . 'm';
    }

    public function formatTime(?Carbon $time): string
    {
        return $time ? $time->format('H:i') : 'N/A';
    }
}
