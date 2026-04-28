@extends('layouts.app')

@section('content')
<div style="padding:1rem;font-family:Inter, Arial, sans-serif;">
    <h2>Attendance Report</h2>
    <p>Generated: {{ now() }}</p>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f3f4f6;text-align:left;">
                <th>ID</th>
                <th>User</th>
                <th>Site</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Status</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                <th>Duration</th>
                <th>Overtime</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $a)
            <tr>
                <td>{{ $a->id }}</td>
                <td>{{ $a->user?->name }}</td>
                <td>{{ $a->site_name }}</td>
                <td>{{ $a->latitude }}</td>
                <td>{{ $a->longitude }}</td>
                <td>{{ $a->status }}</td>
                <td>{{ $a->clock_in_time }}</td>
                <td>{{ $a->clock_out_time ?? '—' }}</td>
                <td>{{ \App\Services\AttendanceMetricsService::formatMinutes(\App\Services\AttendanceMetricsService::workedMinutes($a)) }}</td>
                <td>{{ \App\Services\AttendanceMetricsService::formatMinutes(\App\Services\AttendanceMetricsService::overtimeMinutes($a)) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
