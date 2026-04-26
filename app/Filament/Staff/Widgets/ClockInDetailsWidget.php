<?php

namespace App\Filament\Staff\Widgets;

use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

        $this->stats = Cache::remember('clock_in_details_stats_' . $user->id, 60, function () use ($user, $now, $todayStart, $todayEnd, $weekStart, $weekEnd, $monthStart) {
            $baseQuery = Attendance::where('user_id', $user->id);
            $nowString = $now->toDateTimeString();
            $dbDriver = DB::connection()->getDriverName();

            $sumMinutes = static function ($query, string $nowRef, string $driver): int {
                if ($driver === 'sqlite') {
                    return (int) $query
                        ->get(['clock_in_time', 'clock_out_time'])
                        ->sum(function (Attendance $attendance) use ($nowRef): int {
                            if (!$attendance->clock_in_time) {
                                return 0;
                            }

                            $endTime = $attendance->clock_out_time ?? Carbon::parse($nowRef);

                            if ($endTime->lte($attendance->clock_in_time)) {
                                return 0;
                            }

                            return $attendance->clock_in_time->diffInMinutes($endTime);
                        });
                }

                return (int) $query
                    ->selectRaw('COALESCE(SUM(TIMESTAMPDIFF(MINUTE, clock_in_time, COALESCE(clock_out_time, ?))), 0) as total_minutes', [$nowRef])
                    ->value('total_minutes');
            };

            $todayMinutes = $sumMinutes((clone $baseQuery)->whereBetween('clock_in_time', [$todayStart, $todayEnd]), $nowString, $dbDriver);
            $weekMinutes = $sumMinutes((clone $baseQuery)->whereBetween('clock_in_time', [$weekStart, $weekEnd]), $nowString, $dbDriver);
            $monthMinutes = $sumMinutes((clone $baseQuery)->whereBetween('clock_in_time', [$monthStart, $now]), $nowString, $dbDriver);

            $activeShift = (clone $baseQuery)
                ->whereNull('clock_out_time')
                ->latest('clock_in_time')
                ->first(['id', 'site_name', 'status', 'clock_in_time', 'clock_out_time']);

            $lastShift = (clone $baseQuery)
                ->latest('clock_in_time')
                ->first(['id', 'site_name', 'status', 'clock_in_time', 'clock_out_time']);

            $pendingCount = (clone $baseQuery)
                ->whereIn('status', ['pending', 'temporary'])
                ->count();

            $completedThisMonth = (clone $baseQuery)
                ->whereBetween('clock_in_time', [$monthStart, $now])
                ->whereNotNull('clock_out_time')
                ->count();

            return [
                'todayMinutes' => $todayMinutes,
                'weekMinutes' => $weekMinutes,
                'monthMinutes' => $monthMinutes,
                'activeShift' => $activeShift,
                'activeMinutes' => $activeShift ? $activeShift->clock_in_time->diffInMinutes($now) : 0,
                'lastShift' => $lastShift,
                'lastShiftMinutes' => $lastShift ? $this->durationMinutes($lastShift) : 0,
                'pendingCount' => $pendingCount,
                'completedThisMonth' => $completedThisMonth,
            ];
        });
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
