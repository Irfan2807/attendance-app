<?php

namespace App\Services;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Enums\Role;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\PublicHoliday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MonthlyTimesheetService
{
    /**
     * Check whether an individual attendance started after the grace period.
     */
    public static function isAttendanceLate(Attendance $attendance): bool
    {
        if (! $attendance->clock_in_time) {
            return false;
        }

        $graceMinutes = (int) config('attendance.late_grace_minutes', 15);
        $dayStartHour = (int) config('attendance.day_shift_starts_at', 8);
        $nightStartHour = (int) config('attendance.night_shift_starts_at', 17);

        $dayCutoff = ($dayStartHour * 60) + $graceMinutes;
        $nightCutoff = ($nightStartHour * 60) + $graceMinutes;

        $hour = $attendance->clock_in_time->hour;
        $clockInMinutes = ($hour * 60) + $attendance->clock_in_time->minute;

        if ($hour >= 5 && $hour < 15) {
            return $clockInMinutes > $dayCutoff;
        }

        if ($hour >= 15) {
            return $clockInMinutes > $nightCutoff;
        }

        return false;
    }

    /**
     * Fetch complete month data for an individual user (all days + KPIs).
     */
    public static function getUserMonthlyData(User $user, int $month, int $year, ?Collection $preloadedHolidays = null): array
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();
        $daysInMonth = $startOfMonth->daysInMonth;

        // 1. Fetch non-rejected attendances for the user in this month
        $attendances = Attendance::query()
            ->where('user_id', $user->id)
            ->where('status', '!=', 'rejected')
            ->whereBetween('clock_in_time', [$startOfMonth, $endOfMonth])
            ->orderBy('clock_in_time')
            ->get();

        $attendancesByDate = $attendances->groupBy(function (Attendance $a) {
            return $a->clock_in_time ? $a->clock_in_time->format('Y-m-d') : '';
        });

        // 2. Fetch approved leave requests that intersect with this month
        $leaves = LeaveRequest::query()
            ->where('user_id', $user->id)
            ->where('status', LeaveStatus::Approved)
            ->whereDate('start_date', '<=', $endOfMonth->toDateString())
            ->whereDate('end_date', '>=', $startOfMonth->toDateString())
            ->get();

        // 3. Fetch public holidays in this month (use preloaded collection if supplied)
        $holidays = $preloadedHolidays ?? PublicHoliday::inDateRange($startOfMonth, $endOfMonth)->get();
        $holidaysByDate = $holidays->keyBy(function (PublicHoliday $h) {
            return $h->date instanceof Carbon ? $h->date->format('Y-m-d') : Carbon::parse($h->date)->format('Y-m-d');
        });

        $days = [];
        $totalWorkedMinutes = 0;
        $totalRegularMinutes = 0;
        $totalOvertimeMinutes = 0;
        $daysPresent = 0;
        $lateCount = 0;
        $incompleteCount = 0;

        $annualLeaveDays = 0.0;
        $medicalLeaveDays = 0.0;
        $hospitalizationDays = 0.0;
        $otherLeaveDays = 0.0;
        $expectedWorkdays = 0;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::create($year, $month, $d);
            $dateStr = $date->format('Y-m-d');
            $isWeekend = $date->isWeekend();
            $holiday = $holidaysByDate->get($dateStr);
            $isHoliday = $holiday !== null;

            if (! $isWeekend && ! $isHoliday) {
                $expectedWorkdays++;
            }

            // Check for leave on this day
            $dayLeave = $leaves->first(function (LeaveRequest $lr) use ($dateStr) {
                $start = $lr->start_date instanceof Carbon ? $lr->start_date->format('Y-m-d') : (string) $lr->start_date;
                $end = $lr->end_date instanceof Carbon ? $lr->end_date->format('Y-m-d') : (string) $lr->end_date;

                return $dateStr >= $start && $dateStr <= $end;
            });

            // Check for attendance record on this day
            $dayAttendances = $attendancesByDate->get($dateStr, collect());
            $primaryAttendance = $dayAttendances->first();

            $dayWorkedMinutes = 0;
            $dayOvertimeMinutes = 0;
            $dayRegularMinutes = 0;
            $isLate = false;
            $isIncomplete = false;

            if ($primaryAttendance) {
                $daysPresent++;
                $dayWorkedMinutes = AttendanceMetricsService::workedMinutes($primaryAttendance);
                $dayOvertimeMinutes = AttendanceMetricsService::overtimeMinutes($primaryAttendance);
                $dayRegularMinutes = max(0, $dayWorkedMinutes - $dayOvertimeMinutes);

                $totalWorkedMinutes += $dayWorkedMinutes;
                $totalRegularMinutes += $dayRegularMinutes;
                $totalOvertimeMinutes += $dayOvertimeMinutes;

                $isLate = self::isAttendanceLate($primaryAttendance);
                if ($isLate) {
                    $lateCount++;
                }

                if (! $primaryAttendance->clock_out_time || $primaryAttendance->status === 'temporary') {
                    $isIncomplete = true;
                    $incompleteCount++;
                }
            }

            // Determine primary label and badge style
            $status = 'absent';
            $statusLabel = 'Absent';
            $statusBadge = 'danger';

            if ($primaryAttendance) {
                if ($isLate) {
                    $status = 'late';
                    $statusLabel = 'Late (' . $primaryAttendance->clock_in_time->format('H:i') . ')';
                    $statusBadge = 'warning';
                } elseif ($isIncomplete) {
                    $status = 'incomplete';
                    $statusLabel = 'Incomplete';
                    $statusBadge = 'warning';
                } else {
                    $status = 'present';
                    $statusLabel = 'Present';
                    $statusBadge = 'success';
                }
            } elseif ($dayLeave) {
                $status = 'on_leave';
                $leaveTypeLabel = $dayLeave->leave_type instanceof LeaveType
                    ? $dayLeave->leave_type->label()
                    : (LeaveType::tryFrom((string) $dayLeave->leave_type)?->label() ?? 'Leave');
                $statusLabel = $leaveTypeLabel;
                $statusBadge = 'info';

                if (! $isWeekend && ! $isHoliday) {
                    $typeVal = $dayLeave->leave_type instanceof LeaveType ? $dayLeave->leave_type->value : (string) $dayLeave->leave_type;
                    if ($typeVal === 'annual' || $typeVal === '1') {
                        $annualLeaveDays += 1.0;
                    } elseif ($typeVal === 'medical' || $typeVal === '2') {
                        $medicalLeaveDays += 1.0;
                    } elseif ($typeVal === 'hospitalization' || $typeVal === '3') {
                        $hospitalizationDays += 1.0;
                    } else {
                        $otherLeaveDays += 1.0;
                    }
                }
            } elseif ($isHoliday) {
                $status = 'holiday';
                $statusLabel = $holiday->name;
                $statusBadge = 'secondary';
            } elseif ($isWeekend) {
                $status = 'weekend';
                $statusLabel = 'Rest Day';
                $statusBadge = 'muted';
            }

            $days[] = [
                'day' => $d,
                'date' => $date,
                'date_string' => $dateStr,
                'day_name' => $date->format('D'),
                'is_weekend' => $isWeekend,
                'is_holiday' => $isHoliday,
                'holiday_name' => $holiday?->name,
                'leave' => $dayLeave,
                'attendance' => $primaryAttendance,
                'site_name' => $primaryAttendance?->site_name,
                'clock_in' => $primaryAttendance?->clock_in_time?->format('H:i'),
                'clock_out' => $primaryAttendance?->clock_out_time?->format('H:i'),
                'worked_minutes' => $dayWorkedMinutes,
                'regular_minutes' => $dayRegularMinutes,
                'overtime_minutes' => $dayOvertimeMinutes,
                'status' => $status,
                'status_label' => $statusLabel,
                'status_badge' => $statusBadge,
            ];
        }

        $totalLeaveDays = $annualLeaveDays + $medicalLeaveDays + $hospitalizationDays + $otherLeaveDays;

        return [
            'user' => $user->loadMissing('manager'),
            'month' => $month,
            'year' => $year,
            'month_name' => $startOfMonth->format('F Y'),
            'start_date' => $startOfMonth,
            'end_date' => $endOfMonth,
            'days' => $days,
            'kpi' => [
                'days_in_month' => $daysInMonth,
                'expected_workdays' => $expectedWorkdays,
                'days_present' => $daysPresent,
                'total_worked_minutes' => $totalWorkedMinutes,
                'total_worked_hours' => round($totalWorkedMinutes / 60, 2),
                'total_worked_formatted' => AttendanceMetricsService::formatMinutes($totalWorkedMinutes),
                'total_regular_minutes' => $totalRegularMinutes,
                'total_regular_hours' => round($totalRegularMinutes / 60, 2),
                'total_regular_formatted' => AttendanceMetricsService::formatMinutes($totalRegularMinutes),
                'total_overtime_minutes' => $totalOvertimeMinutes,
                'total_overtime_hours' => round($totalOvertimeMinutes / 60, 2),
                'total_overtime_formatted' => AttendanceMetricsService::formatMinutes($totalOvertimeMinutes),
                'late_count' => $lateCount,
                'incomplete_count' => $incompleteCount,
                'annual_leave_days' => $annualLeaveDays,
                'medical_leave_days' => $medicalLeaveDays,
                'hospitalization_days' => $hospitalizationDays,
                'other_leave_days' => $otherLeaveDays,
                'total_leave_days' => $totalLeaveDays,
            ],
        ];
    }

    /**
     * Get aggregate monthly summary for all matching users (for HR overview table).
     */
    public static function getCompanyMonthlySummary(
        int $month,
        int $year,
        ?int $role = null,
        ?int $managerId = null,
        ?string $search = null
    ): array {
        $usersQuery = User::query()
            ->where('is_active', true)
            ->when($role, fn (Builder $q) => $q->where('role', $role))
            ->when($managerId, fn (Builder $q) => $q->where('manager_id', $managerId))
            ->when($search, function (Builder $q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('name', 'like', "%{$s}%")
                        ->orWhere('phone', 'like', "%{$s}%");
                });
            })
            ->orderBy('name');

        $users = $usersQuery->get();

        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();
        $preloadedHolidays = PublicHoliday::inDateRange($startOfMonth, $endOfMonth)->get();

        $staffSummaries = [];
        $companyTotals = [
            'staff_count' => $users->count(),
            'total_present_days' => 0,
            'total_regular_hours' => 0.0,
            'total_overtime_hours' => 0.0,
            'total_worked_hours' => 0.0,
            'total_leave_days' => 0.0,
            'total_late_count' => 0,
        ];

        foreach ($users as $u) {
            $data = self::getUserMonthlyData($u, $month, $year, $preloadedHolidays);
            $kpi = $data['kpi'];

            $staffSummaries[] = [
                'user' => $u,
                'kpi' => $kpi,
            ];

            $companyTotals['total_present_days'] += $kpi['days_present'];
            $companyTotals['total_regular_hours'] += $kpi['total_regular_hours'];
            $companyTotals['total_overtime_hours'] += $kpi['total_overtime_hours'];
            $companyTotals['total_worked_hours'] += $kpi['total_worked_hours'];
            $companyTotals['total_leave_days'] += $kpi['total_leave_days'];
            $companyTotals['total_late_count'] += $kpi['late_count'];
        }

        return [
            'month' => $month,
            'year' => $year,
            'month_name' => Carbon::create($year, $month, 1)->format('F Y'),
            'totals' => $companyTotals,
            'staff' => $staffSummaries,
        ];
    }
}

