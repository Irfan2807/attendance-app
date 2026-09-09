<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report - Tumpat Solutions</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1f2937;
            background: #fff;
            padding: 24px;
            font-size: 13px;
            line-height: 1.5;
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .report-title h1 {
            font-size: 22px;
            color: #111827;
            font-weight: 700;
        }
        .report-title p {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }
        .report-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
        }
        .btn-primary {
            background-color: #F27E26;
            color: #fff;
        }
        .btn-primary:hover {
            background-color: #d66d1f;
        }
        .btn-secondary {
            background-color: #f3f4f6;
            color: #374151;
            border-color: #d1d5db;
        }
        .btn-secondary:hover {
            background-color: #e5e7eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        tr:hover {
            background-color: #f9fafb;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
        }
        .badge-approved, .badge-completed {
            background-color: #dcfce7;
            color: #15803d;
        }
        .badge-pending, .badge-temporary {
            background-color: #fef9c3;
            color: #854d0e;
        }
        .badge-rejected {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .pagination-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #6b7280;
            margin-top: 16px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            th {
                background-color: #f3f4f6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="report-header">
        <div class="report-title">
            <h1>Attendance Report</h1>
            <p>Tumpat Solutions Sdn Bhd &bull; Generated on {{ now()->format('d M Y, h:i A') }}</p>
        </div>
        <div class="report-actions no-print">
            <button class="btn btn-primary" onclick="window.print()">Print Report</button>
            <a href="javascript:window.close()" class="btn btn-secondary">Close</a>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Staff Member</th>
                <th>Location</th>
                <th>Status</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                <th>Duration</th>
                <th>Overtime</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $a)
            <tr>
                <td>#{{ $a->id }}</td>
                <td><strong>{{ $a->user?->name ?? '—' }}</strong></td>
                <td>{{ $a->site_name ?? '—' }}</td>
                <td>
                    <span class="badge badge-{{ $a->status }}">{{ $a->status }}</span>
                </td>
                <td>{{ $a->clock_in_time ? $a->clock_in_time->format('d/m/Y H:i') : '—' }}</td>
                <td>{{ $a->clock_out_time ? $a->clock_out_time->format('d/m/Y H:i') : '—' }}</td>
                <td>{{ \App\Services\AttendanceMetricsService::formatMinutes(\App\Services\AttendanceMetricsService::workedMinutes($a)) }}</td>
                <td>{{ \App\Services\AttendanceMetricsService::formatMinutes(\App\Services\AttendanceMetricsService::overtimeMinutes($a)) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; color: #9ca3af; padding: 24px;">No attendance records found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-bar no-print">
        <div>Showing {{ $attendances->firstItem() ?? 0 }} to {{ $attendances->lastItem() ?? 0 }} of {{ $attendances->total() }} records</div>
        <div>
            @if ($attendances->hasPages())
                {{ $attendances->links() }}
            @endif
        </div>
    </div>
</body>
</html>
