<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use App\Services\MonthlyTimesheetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonthlyReportController extends Controller
{
    /**
     * Display the HR & Manager Monthly Payroll and Timesheet Hub.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        if (! $currentUser || ! $currentUser->isManagerOrAdmin()) {
            abort(403, 'Unauthorized access to payroll and timesheet reports.');
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $role = $request->filled('role') ? (int) $request->input('role') : null;
        $search = $request->input('search');

        // Managers only view their subordinates unless they are Admin or HR
        $managerId = ($currentUser->isManager() && ! $currentUser->isAdmin() && ! $currentUser->isHr())
            ? $currentUser->id
            : null;

        $reportData = MonthlyTimesheetService::getCompanyMonthlySummary(
            $month,
            $year,
            $role,
            $managerId,
            $search
        );

        $availableYears = range(now()->year - 2, now()->year + 1);

        return view('attendance.monthly-index', compact('reportData', 'month', 'year', 'role', 'search', 'availableYears'));
    }

    /**
     * Display a single employee's printable A4 monthly attendance card.
     */
    public function individualSlip(Request $request, User $user)
    {
        $currentUser = Auth::user();
        if (! $currentUser) {
            abort(401);
        }

        // Authorization: Admin, HR, assigned manager, or the employee themselves
        $canAccess = $currentUser->isAdmin()
            || $currentUser->isHr()
            || ($currentUser->isManager() && $user->manager_id === $currentUser->id)
            || $currentUser->id === $user->id;

        if (! $canAccess) {
            abort(403, 'Unauthorized to view this attendance timesheet.');
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $timesheet = MonthlyTimesheetService::getUserMonthlyData($user, $month, $year);

        return view('attendance.monthly-slip', [
            'is_bundle' => false,
            'timesheet' => $timesheet,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Generate a multi-page printable bundle of all active staff timesheets.
     */
    public function payrollBundle(Request $request)
    {
        $currentUser = Auth::user();
        if (! $currentUser || ! $currentUser->isManagerOrAdmin()) {
            abort(403, 'Unauthorized access to payroll bundle.');
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $role = $request->filled('role') ? (int) $request->input('role') : null;

        $managerId = ($currentUser->isManager() && ! $currentUser->isAdmin() && ! $currentUser->isHr())
            ? $currentUser->id
            : null;

        $users = User::query()
            ->where('is_active', true)
            ->when($role, fn ($q) => $q->where('role', $role))
            ->when($managerId, fn ($q) => $q->where('manager_id', $managerId))
            ->orderBy('name')
            ->get();

        $bundle = [];
        foreach ($users as $user) {
            $bundle[] = MonthlyTimesheetService::getUserMonthlyData($user, $month, $year);
        }

        return view('attendance.monthly-slip', [
            'is_bundle' => true,
            'bundle' => $bundle,
            'month' => $month,
            'year' => $year,
            'month_name' => Carbon::create($year, $month, 1)->format('F Y'),
        ]);
    }

    /**
     * Export monthly summary metrics as a CSV file for payroll software.
     */
    public function exportSummaryCsv(Request $request)
    {
        $currentUser = Auth::user();
        if (! $currentUser || ! $currentUser->isManagerOrAdmin()) {
            abort(403);
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $role = $request->filled('role') ? (int) $request->input('role') : null;
        $search = $request->input('search');

        $managerId = ($currentUser->isManager() && ! $currentUser->isAdmin() && ! $currentUser->isHr())
            ? $currentUser->id
            : null;

        $reportData = MonthlyTimesheetService::getCompanyMonthlySummary(
            $month,
            $year,
            $role,
            $managerId,
            $search
        );

        $monthStr = Carbon::create($year, $month, 1)->format('Y_m');
        $fileName = "payroll_summary_{$monthStr}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($reportData) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'Staff ID',
                'Employee Name',
                'Phone',
                'Designation',
                'Supervisor',
                'Days Present',
                'Regular Hours',
                'Overtime Hours',
                'Total Hours',
                'Annual Leave (Days)',
                'Medical Leave (Days)',
                'Other Leaves (Days)',
                'Total Leaves (Days)',
                'Late Starts',
                'Incomplete Clock-outs',
            ]);

            foreach ($reportData['staff'] as $item) {
                /** @var User $user */
                $user = $item['user'];
                $kpi = $item['kpi'];

                $roleName = match ($user->roleValue()) {
                    1 => 'Super Admin',
                    2 => 'Operations Manager',
                    3 => 'Field Engineer / Staff',
                    4 => 'HR Executive',
                    default => 'Staff',
                };

                fputcsv($handle, [
                    $user->id,
                    $this->sanitizeCsvField($user->name),
                    $this->sanitizeCsvField($user->phone),
                    $roleName,
                    $this->sanitizeCsvField($user->manager?->name ?? 'None'),
                    $kpi['days_present'],
                    $kpi['total_regular_hours'],
                    $kpi['total_overtime_hours'],
                    $kpi['total_worked_hours'],
                    $kpi['annual_leave_days'],
                    $kpi['medical_leave_days'],
                    $kpi['hospitalization_days'] + $kpi['other_leave_days'],
                    $kpi['total_leave_days'],
                    $kpi['late_count'],
                    $kpi['incomplete_count'],
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function sanitizeCsvField(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        // Prevent CSV formula injection (CWE-1236)
        if (preg_match('/^[=+\-@\t\r]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }
}

