<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Attendance;
use App\Models\AttendanceInfraction;
use App\Models\Site;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AttendanceAnalyticsService
{
    public static function operationalDayRange(?Carbon $reference = null): array
    {
        return AttendanceWindowService::operationalDayRange($reference, 'day');
    }

    public static function rollingRange(int $days, ?Carbon $reference = null): array
    {
        $safeDays = max(1, $days);
        $bufferHours = (int) config('attendance.early_arrival_buffer_hours', 2);
        $end = AttendanceWindowService::operationalDayEnd($reference, 'day')->subHours($bufferHours);

        return [$end->copy()->subDays($safeDays), $end];
    }

    public static function attendanceRate(Carbon $start, Carbon $end): array
    {
        $totalStaff = User::where('role', Role::Staff->value)->where('is_active', true)->count();
        $presentStaff = self::staffAttendanceQuery()
            ->where('status', '!=', 'rejected')
            ->whereBetween('clock_in_time', [$start, $end])
            ->distinct('user_id')
            ->count('user_id');

        $startDateStr = $start->toDateString();
        $onLeaveStaff = User::where('role', Role::Staff->value)
            ->where('is_active', true)
            ->whereHas('leaveRequests', function ($q) use ($startDateStr) {
                $q->where('status', \App\Enums\LeaveStatus::Approved->value)
                    ->whereDate('start_date', '<=', $startDateStr)
                    ->whereDate('end_date', '>=', $startDateStr);
            })
            ->count();

        $expectedStaff = max(0, $totalStaff - $onLeaveStaff);
        $adjustedRate = $expectedStaff > 0
            ? round(($presentStaff / $expectedStaff) * 100, 1)
            : ($totalStaff > 0 ? 100.0 : 0.0);

        $rate = $totalStaff > 0
            ? round(($presentStaff / $totalStaff) * 100, 1)
            : 0.0;

        return [
            'rate' => $rate,
            'adjusted_rate' => $adjustedRate,
            'present' => $presentStaff,
            'on_leave' => $onLeaveStaff,
            'expected' => $expectedStaff,
            'total' => $totalStaff,
        ];
    }

    public static function staffOnLeaveCount(?Carbon $date = null): int
    {
        $dateStr = ($date ?? now())->toDateString();

        return User::where('role', Role::Staff->value)
            ->where('is_active', true)
            ->whereHas('leaveRequests', function ($q) use ($dateStr) {
                $q->where('status', \App\Enums\LeaveStatus::Approved->value)
                    ->whereDate('start_date', '<=', $dateStr)
                    ->whereDate('end_date', '>=', $dateStr);
            })
            ->count();
    }

    public static function attendanceRateTrend(int $days, ?Carbon $reference = null): array
    {
        $labels = [];
        $rates = [];

        foreach (self::operationalBuckets($days, $reference) as $bucket) {
            $labels[] = $bucket['label'];
            $rates[] = self::attendanceRate($bucket['start'], $bucket['end'])['rate'];
        }

        return [
            'labels' => $labels,
            'rates' => $rates,
        ];
    }

    public static function overtimeMinutes(Carbon $start, Carbon $end): int
    {
        return self::staffAttendanceQuery()
            ->where('status', '!=', 'rejected')
            ->whereBetween('clock_in_time', [$start, $end])
            ->whereNotNull('clock_out_time')
            ->get(['clock_in_time', 'clock_out_time'])
            ->sum(fn (Attendance $attendance) => AttendanceMetricsService::overtimeMinutes($attendance));
    }

    public static function overtimeTrend(int $days, ?Carbon $reference = null): array
    {
        $labels = [];
        $hours = [];

        foreach (self::operationalBuckets($days, $reference) as $bucket) {
            $labels[] = $bucket['label'];
            $minutes = self::overtimeMinutes($bucket['start'], $bucket['end']);
            $hours[] = round($minutes / 60, 1);
        }

        return [
            'labels' => $labels,
            'hours' => $hours,
        ];
    }

    public static function lateStartsTrend(int $days, ?Carbon $reference = null): array
    {
        $labels = [];
        $counts = [];

        foreach (self::operationalBuckets($days, $reference) as $bucket) {
            $labels[] = $bucket['label'];
            $counts[] = self::lateStartsCount($bucket['start'], $bucket['end']);
        }

        return [
            'labels' => $labels,
            'counts' => $counts,
        ];
    }

    public static function punctualityRate(Carbon $start, Carbon $end): array
    {
        $presentStaff = self::staffAttendanceQuery()
            ->where('status', '!=', 'rejected')
            ->whereBetween('clock_in_time', [$start, $end])
            ->distinct('user_id')
            ->count('user_id');

        $lateCount = self::lateStartsCount($start, $end);
        $onTimeCount = max(0, $presentStaff - $lateCount);

        $rate = $presentStaff > 0
            ? round(($onTimeCount / $presentStaff) * 100, 1)
            : 0.0;

        return [
            'rate' => $rate,
            'on_time' => $onTimeCount,
            'late' => $lateCount,
            'present' => $presentStaff,
        ];
    }

    public static function punctualityRateTrend(int $days, ?Carbon $reference = null): array
    {
        $labels = [];
        $rates = [];

        foreach (self::operationalBuckets($days, $reference) as $bucket) {
            $labels[] = $bucket['label'];
            $rates[] = self::punctualityRate($bucket['start'], $bucket['end'])['rate'];
        }

        return [
            'labels' => $labels,
            'rates' => $rates,
        ];
    }

    public static function pendingApprovalMetrics(?Carbon $reference = null): array
    {
        $now = $reference?->copy() ?? now();
        $base = self::staffAttendanceQuery()
            ->whereIn('status', ['pending', 'temporary']);

        return [
            'total' => (clone $base)->count(),
            'older_than_24h' => (clone $base)->where('clock_in_time', '<', $now->copy()->subHours(24))->count(),
            'older_than_48h' => (clone $base)->where('clock_in_time', '<', $now->copy()->subHours(48))->count(),
        ];
    }

    public static function approvalAnalytics(Carbon $start, Carbon $end): array
    {
        $decisions = self::staffAttendanceQuery()
            ->whereIn('status', ['approved', 'rejected'])
            ->whereBetween('approved_at', [$start, $end])
            ->whereNotNull('approved_at')
            ->get(['status', 'approved_at', 'clock_in_time', 'clock_out_time', 'approved_by']);

        $approvedCount = $decisions->where('status', 'approved')->count();
        $rejectedCount = $decisions->where('status', 'rejected')->count();
        $decisionCount = $approvedCount + $rejectedCount;

        $validTurnaroundRecords = $decisions->filter(fn (Attendance $a) => ($a->clock_out_time || $a->clock_in_time) && $a->approved_at);

        $avgTurnaroundMinutes = $validTurnaroundRecords->isNotEmpty()
            ? round($validTurnaroundRecords->avg(function (Attendance $attendance): float {
                $submissionTime = $attendance->clock_out_time ?? $attendance->clock_in_time;

                return (float) max(0, $submissionTime->diffInMinutes($attendance->approved_at));
            }), 1)
            : 0.0;

        return [
            'approved_count' => $approvedCount,
            'rejected_count' => $rejectedCount,
            'decision_count' => $decisionCount,
            'approval_rate' => $decisionCount > 0 ? round(($approvedCount / $decisionCount) * 100, 1) : 0.0,
            'rejection_rate' => $decisionCount > 0 ? round(($rejectedCount / $decisionCount) * 100, 1) : 0.0,
            'avg_turnaround_minutes' => $avgTurnaroundMinutes,
            'top_manager_summary' => self::topManagerApprovalSummary($decisions),
        ];
    }

    public static function approvalTurnaroundTrend(int $days, ?Carbon $reference = null): array
    {
        $labels = [];
        $hours = [];

        foreach (self::operationalBuckets($days, $reference) as $bucket) {
            $labels[] = $bucket['label'];
            $metrics = self::approvalAnalytics($bucket['start'], $bucket['end']);
            $hours[] = round($metrics['avg_turnaround_minutes'] / 60, 1);
        }

        return [
            'labels' => $labels,
            'hours' => $hours,
            'minutes' => array_map(fn ($h) => round($h * 60, 1), $hours),
        ];
    }

    public static function siteCoverage(Carbon $start, Carbon $end): array
    {
        $activeSiteNames = Site::query()
            ->where('is_active', true)
            ->pluck('name')
            ->filter()
            ->values();

        $totalActiveSites = $activeSiteNames->count();

        $activeSitesCount = $totalActiveSites > 0
            ? self::staffAttendanceQuery()
                ->where('status', '!=', 'rejected')
                ->whereBetween('clock_in_time', [$start, $end])
                ->whereIn('site_name', $activeSiteNames)
                ->distinct('site_name')
                ->count('site_name')
            : 0;

        $coverage = $totalActiveSites > 0
            ? min(100.0, round(($activeSitesCount / $totalActiveSites) * 100, 1))
            : 0.0;

        $topSite = self::staffAttendanceQuery()
            ->where('status', '!=', 'rejected')
            ->whereBetween('clock_in_time', [$start, $end])
            ->whereNotNull('site_name')
            ->where('site_name', '!=', '')
            ->selectRaw('site_name, COUNT(*) as total')
            ->groupBy('site_name')
            ->orderByDesc('total')
            ->first();

        return [
            'active_sites' => $activeSitesCount,
            'total_active_sites' => $totalActiveSites,
            'has_configured_sites' => $totalActiveSites > 0,
            'coverage_rate' => $coverage,
            'top_site_name' => $topSite->site_name ?? '—',
            'top_site_count' => (int) ($topSite->total ?? 0),
        ];
    }

    public static function dataQualityMetrics(Carbon $start, Carbon $end): array
    {
        $base = self::staffAttendanceQuery()->whereBetween('clock_in_time', [$start, $end]);

        $missingCoordinates = (clone $base)
            ->where(function (Builder $query): void {
                $query->whereNull('latitude')
                    ->orWhereNull('longitude')
                    ->orWhere('latitude', 0)
                    ->orWhere('longitude', 0);
            })
            ->count();

        $autoClosedStale = AttendanceInfraction::query()
            ->whereBetween('created_at', [$start, $end])
            ->where(function (Builder $query): void {
                $query->where('infraction_type', 'like', 'auto_clock_out%')
                    ->orWhere('infraction_type', 'forgot_clock_out');
            })
            ->count();

        $repeatedTemporaryUsers = (clone $base)
            ->where('status', 'temporary')
            ->selectRaw('user_id, COUNT(*) as temporary_count')
            ->groupBy('user_id')
            ->having('temporary_count', '>=', 2)
            ->get()
            ->count();

        $staleTemporaryShifts = (clone $base)
            ->where('status', 'temporary')
            ->where('clock_in_time', '<', now()->subHours(48))
            ->count();

        $repeatOffender = AttendanceInfraction::query()
            ->with('user:id,name')
            ->whereBetween('created_at', [$start, $end])
            ->get(['user_id'])
            ->groupBy('user_id')
            ->sortByDesc(fn (Collection $group) => $group->count())
            ->map(function (Collection $group, $userId): array {
                $record = $group->first();

                return [
                    'user_id' => (int) $userId,
                    'name' => $record?->user?->name ?? 'Unknown',
                    'count' => $group->count(),
                ];
            })
            ->first();

        return [
            'missing_coordinates' => $missingCoordinates,
            'auto_closed_stale' => $autoClosedStale,
            'repeated_temporary_users' => $repeatedTemporaryUsers,
            'stale_temporary_shifts' => $staleTemporaryShifts,
            'repeat_offender_name' => $repeatOffender['name'] ?? '—',
            'repeat_offender_count' => $repeatOffender['count'] ?? 0,
        ];
    }

    public static function infractionCount(Carbon $start, Carbon $end): int
    {
        return AttendanceInfraction::query()
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    private static function staffAttendanceQuery(): Builder
    {
        return Attendance::query()->whereHas('user', fn (Builder $query) => $query->where('role', Role::Staff->value));
    }

    private static function operationalBuckets(int $days, ?Carbon $reference = null): array
    {
        $safeDays = max(1, $days);
        $bufferHours = (int) config('attendance.early_arrival_buffer_hours', 2);
        $endBoundary = AttendanceWindowService::operationalDayEnd($reference, 'day')->subHours($bufferHours);
        $buckets = [];

        for ($dayOffset = $safeDays - 1; $dayOffset >= 0; $dayOffset--) {
            $bucketEnd = $endBoundary->copy()->subDays($dayOffset);
            $bucketStart = $bucketEnd->copy()->subDay();
            $buckets[] = [
                'start' => $bucketStart,
                'end' => $bucketEnd,
                'label' => $bucketStart->format('d M'),
            ];
        }

        return $buckets;
    }

    private static function lateStartsCount(Carbon $start, Carbon $end): int
    {
        $graceMinutes = (int) config('attendance.late_grace_minutes', 15);
        $dayStartHour = (int) config('attendance.day_shift_starts_at', 8);
        $nightStartHour = (int) config('attendance.night_shift_starts_at', 17);

        $dayCutoffMinutes = ($dayStartHour * 60) + $graceMinutes;
        $nightCutoffMinutes = ($nightStartHour * 60) + $graceMinutes;

        return self::staffAttendanceQuery()
            ->where('status', '!=', 'rejected')
            ->whereBetween('clock_in_time', [$start, $end])
            ->get(['clock_in_time'])
            ->filter(function (Attendance $attendance) use ($dayCutoffMinutes, $nightCutoffMinutes): bool {
                if (! $attendance->clock_in_time) {
                    return false;
                }

                $hour = $attendance->clock_in_time->hour;
                $clockInMinutes = ($hour * 60) + $attendance->clock_in_time->minute;

                // Day shift window (05:00 to 14:59)
                if ($hour >= 5 && $hour < 15) {
                    return $clockInMinutes > $dayCutoffMinutes;
                }

                // Night shift window (15:00 to 23:59)
                if ($hour >= 15) {
                    return $clockInMinutes > $nightCutoffMinutes;
                }

                return false;
            })
            ->count();
    }

    private static function topManagerApprovalSummary(Collection $decisions): string
    {
        $grouped = $decisions
            ->filter(fn (Attendance $attendance): bool => (int) $attendance->approved_by > 0)
            ->groupBy('approved_by')
            ->map(fn (Collection $records): int => $records->count())
            ->sortDesc();

        if ($grouped->isEmpty()) {
            return 'No manager decisions yet';
        }

        $topManagerId = (int) $grouped->keys()->first();
        $topCount = (int) $grouped->first();
        $topManager = User::query()->find($topManagerId);

        return ($topManager?->name ?? 'Unknown').' ('.$topCount.' decisions)';
    }
}
