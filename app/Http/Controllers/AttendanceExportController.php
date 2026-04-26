<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Services\AttendanceMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceExportController extends Controller
{
    public function exportCsv(Request $request)
    {
        if (! Auth::user() || ! in_array(Auth::user()->role, [1,2])) {
            abort(403);
        }

        $fileName = 'attendances_' . now()->format('Ymd_His') . '.csv';
        // Eager load relationships and paginate for memory efficiency with large datasets
        $attendances = Attendance::with('user')
            ->orderBy('clock_in_time', 'desc')
            ->lazy(100); // Process in chunks of 100 to save memory

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        $callback = function () use ($attendances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id','user','site_name','latitude','longitude','status','clock_in_time','clock_out_time','duration_minutes','overtime_minutes']);
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
        if (! Auth::user() || ! in_array(Auth::user()->role, [1,2])) {
            abort(403);
        }

        // Paginate for better performance with large datasets (25 per page)
        $attendances = Attendance::with('user')
            ->orderBy('clock_in_time', 'desc')
            ->paginate(25);

        return view('attendance.print', compact('attendances'));
    }
}
