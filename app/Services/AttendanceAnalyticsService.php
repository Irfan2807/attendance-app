<?php

namespace App\Services;

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
        $end = AttendanceWindowService::operationalDayEnd($reference, 'day');

        return [$end->copy()->subDays($safeDays), $end];
    }

    public static function attendanceRate(Carbon $start, Carbon $end): array
    {
        $totalStaff = User::where('role', 3)->count();
        $presentStaff = self::staffAttendanceQuery()
            ->whereBetween('clock_in_time', [$start, $end])
            ->distinct('user_id')
            ->count('user_id');

        $rate = $totalStaff > 0
            ? round(($presentStaff / $totalStaff) * 100, 1)
            : 0.0;

        return [
            'rate' => $rate,
            'present' => $presentStaff,
            'total' => $totalStaff,
        ];
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
            ->get(['status', 'approved_at', 'clock_in_time', 'approved_by']);

        $approvedCount = $decisions->where('status', 'approved')->count();
        $rejectedCount = $decisions->where('status', 'rejected')->count();
        $decisionCount = $approvedCount + $rejectedCount;

        $avgTurnaroundMinutes = $decisions->isNotEmpty()
            ? round($decisions->avg(function (Attendance $attendance): float {
                if (! $attendance->clock_in_time || ! $attendance->approved_at) {
                    return 0;
                }

                return (float) $attendance->clock_in_time->diffInMinutes($attendance->approved_at);
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
        $minutes = [];

        foreach (self::operationalBuckets($days, $reference) as $bucket) {
            $labels[] = $bucket['label'];
            $metrics = self::approvalAnalytics($bucket['start'], $bucket['end']);
            $minutes[] = $metrics['avg_turnaround_minutes'];
        }

        return [
            'labels' => $labels,
            'minutes' => $minutes,
        ];
    }

    public static function siteCoverage(Carbon $start, Carbon $end): array
    {
        $siteStats = self::staffAttendanceQuery()
            ->whereBetween('clock_in_time', [$start, $end])
            ->whereNotNull('site_name')
            ->where('site_name', '!=', '')
            ->selectRaw('site_name, COUNT(*) as total')
            ->groupBy('site_name')
            ->orderByDesc('total')
            ->get();

        $activeSites = $siteStats->count();
        $totalActiveSites = Site::query()->where('is_active', true)->count();
        $coverage = $totalActiveSites > 0
            ? round(($activeSites / $totalActiveSites) * 100, 1)
            : 0.0;

        $topSite = $siteStats->first();

        return [
            'active_sites' => $activeSites,
            'total_active_sites' => $totalActiveSites,
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

        $autoClosedStale = (clone $base)
            ->where('verification_notes', 'like', '%Auto-closed stale shift%')
            ->count();

        $repeatedTemporaryUsers = (clone $base)
            ->where('status', 'temporary')
            ->selectRaw('user_id, COUNT(*) as temporary_count')
            ->groupBy('user_id')
            ->having('temporary_count', '>=', 2)
            ->get()
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
        return Attendance::query()->whereHas('user', fn (Builder $query) => $query->where('role', 3));
    }

    private static function operationalBuckets(int $days, ?Carbon $reference = null): array
    {
        $safeDays = max(1, $days);
        $endBoundary = AttendanceWindowService::operationalDayEnd($reference, 'day');
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
        $shiftStartMinutes = ((int) config('attendance.day_shift_starts_at', 8) * 60) + $graceMinutes;

        return self::staffAttendanceQuery()
            ->whereBetween('clock_in_time', [$start, $end])
            ->get(['clock_in_time'])
            ->filter(function (Attendance $attendance) use ($shiftStartMinutes): bool {
                if (! $attendance->clock_in_time) {
                    return false;
                }

                $clockInMinutes = ($attendance->clock_in_time->hour * 60) + $attendance->clock_in_time->minute;

                return $clockInMinutes > $shiftStartMinutes;
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
