<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Services\AttendanceMetricsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceExportController extends Controller
{
    public function exportCsv(Request $request)
    {
        if (! Auth::user() || ! in_array(Auth::user()->role, [1, 2])) {
            abort(403);
        }

        $fileName = 'attendances_'.now()->format('Ymd_His').'.csv';
        $attendances = $this->filteredAttendancesQuery($request)
            ->orderByDesc('clock_in_time')
            ->lazy(100); // Process in chunks of 100 to save memory

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        $callback = function () use ($attendances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'user', 'site_name', 'latitude', 'longitude', 'status', 'clock_in_time', 'clock_out_time', 'duration_minutes', 'overtime_minutes']);
            foreach ($attendances as $a) {
                $durationMinutes = AttendanceMetricsService::workedMinutes($a);
                $overtimeMinutes = AttendanceMetricsService::overtimeMinutes($a);

                fputcsv($handle, [
                    $a->id,
                    $a->user?->name,
                    $a->site_name,
                    $a->latitude,
                    $a->longitude,
                    $a->status,
                    $a->clock_in_time,
                    $a->clock_out_time,
                    $durationMinutes,
                    $overtimeMinutes,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printView()
    {
        if (! Auth::user() || ! in_array(Auth::user()->role, [1, 2])) {
            abort(403);
        }

        $attendances = $this->filteredAttendancesQuery(request())
            ->orderByDesc('clock_in_time')
            ->paginate(25)
            ->withQueryString();

        return view('attendance.print', compact('attendances'));
    }

    private function filteredAttendancesQuery(Request $request): Builder
    {
        return Attendance::query()
            ->with('user')
            ->when(
                $request->filled('status'),
                fn (Builder $query) => $query->where('status', $request->string('status')->toString())
            )
            ->when(
                $request->filled('user_id'),
                fn (Builder $query) => $query->where('user_id', (int) $request->input('user_id'))
            )
            ->when(
                $request->filled('approved_by'),
                fn (Builder $query) => $query->where('approved_by', (int) $request->input('approved_by'))
            )
            ->when(
                $request->filled('site_name'),
                fn (Builder $query) => $query->where('site_name', $request->string('site_name')->toString())
            )
            ->when(
                $request->filled('role'),
                fn (Builder $query) => $query->whereHas('user', fn (Builder $userQuery) => $userQuery->where('role', (int) $request->input('role')))
            )
            ->when(
                $request->filled('from'),
                fn (Builder $query) => $query->whereDate('clock_in_time', '>=', $request->date('from'))
            )
            ->when(
                $request->filled('until'),
                fn (Builder $query) => $query->whereDate('clock_in_time', '<=', $request->date('until'))
            );
    }
}
