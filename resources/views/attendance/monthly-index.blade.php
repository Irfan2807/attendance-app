<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Payroll &amp; Timesheet Hub - Tumpat Solutions</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|space-grotesk:500,600,700" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
    </style>
</head>
<body class="min-h-screen antialiased flex flex-col justify-between">
    <div>
        <!-- Top Navigation Bar -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-wrap justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ auth()->user()->dashboardPath() }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 transition-colors text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Portal
                    </a>
                    <span class="text-slate-300">|</span>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex p-1.5 rounded-lg bg-orange-50 text-orange-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-base font-bold text-slate-900 leading-tight">Monthly Payroll &amp; Timesheet Hub</h1>
                            <p class="text-xs text-slate-500">Tumpat Solutions Sdn Bhd &bull; Operations & Field Verification</p>
                        </div>
                    </div>
                </div>

                <!-- Export & Print Actions -->
                <div class="flex items-center gap-2.5">
                    <a href="{{ route('attendance.monthly.csv', request()->query()) }}" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-sm transition-all">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Export Summary CSV
                    </a>
                    <a href="{{ route('attendance.monthly.bundle', request()->query()) }}" 
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg text-white shadow-sm transition-all"
                       style="background: linear-gradient(135deg, #f7a04c, #f27e26);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print All Staff (Payroll Bundle)
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content Container -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            <!-- Filter Bar -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
                <form method="GET" action="{{ route('attendance.monthly.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                    <!-- Month Selector -->
                    <div>
                        <label for="month" class="block text-xs font-semibold text-slate-600 mb-1 uppercase tracking-wider">Payroll Month</label>
                        <select name="month" id="month" class="w-full text-xs font-medium rounded-lg border-slate-300 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 py-2">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <!-- Year Selector -->
                    <div>
                        <label for="year" class="block text-xs font-semibold text-slate-600 mb-1 uppercase tracking-wider">Year</label>
                        <select name="year" id="year" class="w-full text-xs font-medium rounded-lg border-slate-300 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 py-2">
                            @foreach ($availableYears as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Role Filter -->
                    <div>
                        <label for="role" class="block text-xs font-semibold text-slate-600 mb-1 uppercase tracking-wider">Department / Role</label>
                        <select name="role" id="role" class="w-full text-xs font-medium rounded-lg border-slate-300 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 py-2">
                            <option value="">All Roles</option>
                            <option value="3" {{ $role == 3 ? 'selected' : '' }}>Field Engineers & Staff</option>
                            <option value="2" {{ $role == 2 ? 'selected' : '' }}>Operations Managers</option>
                            <option value="4" {{ $role == 4 ? 'selected' : '' }}>HR Executives</option>
                            <option value="1" {{ $role == 1 ? 'selected' : '' }}>Super Admins</option>
                        </select>
                    </div>

                    <!-- Staff Search -->
                    <div>
                        <label for="search" class="block text-xs font-semibold text-slate-600 mb-1 uppercase tracking-wider">Search Staff</label>
                        <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Name or mobile..." class="w-full text-xs rounded-lg border-slate-300 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 py-2">
                    </div>

                    <!-- Submit & Reset -->
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 py-2 px-3 text-xs font-semibold rounded-lg text-white transition-all shadow-sm flex items-center justify-center gap-1.5" style="background: #F27E26;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Apply Filter
                        </button>
                        <a href="{{ route('attendance.monthly.index') }}" class="py-2 px-3 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors flex items-center justify-center">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Company Overview KPI Banner -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm">
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Active Staff</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-1">{{ $reportData['totals']['staff_count'] }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $reportData['month_name'] }}</p>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm">
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Present Days</p>
                    <p class="text-xl font-extrabold text-emerald-600 mt-1">{{ $reportData['totals']['total_present_days'] }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Total shifts logged</p>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm">
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Regular Hours</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-1">{{ number_format($reportData['totals']['total_regular_hours'], 1) }}h</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Standard shift hours</p>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm">
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Overtime (OT)</p>
                    <p class="text-xl font-extrabold text-orange-600 mt-1">{{ number_format($reportData['totals']['total_overtime_hours'], 1) }}h</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Extra billable hours</p>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm">
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Leaves Taken</p>
                    <p class="text-xl font-extrabold text-blue-600 mt-1">{{ $reportData['totals']['total_leave_days'] }} <span class="text-xs font-normal">days</span></p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Approved applications</p>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-3.5 shadow-sm">
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Late Starts</p>
                    <p class="text-xl font-extrabold text-rose-600 mt-1">{{ $reportData['totals']['total_late_count'] }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Past 15m grace window</p>
                </div>
            </div>

            <!-- Employee Payroll Summary Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Staff Monthly Attendance Roster</h2>
                        <p class="text-xs text-slate-500">Period: <span class="font-semibold text-slate-700">{{ $reportData['month_name'] }}</span> &bull; Showing {{ count($reportData['staff']) }} employee(s)</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200 text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Employee</th>
                                <th class="px-3 py-3">Designation</th>
                                <th class="px-3 py-3">Supervisor</th>
                                <th class="px-3 py-3 text-center">Days Present</th>
                                <th class="px-3 py-3 text-right">Regular Hrs</th>
                                <th class="px-3 py-3 text-right">Overtime Hrs</th>
                                <th class="px-3 py-3 text-right">Total Hours</th>
                                <th class="px-3 py-3 text-center">Leaves</th>
                                <th class="px-3 py-3 text-center">Late / Issues</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($reportData['staff'] as $item)
                                @php
                                    $user = $item['user'];
                                    $kpi = $item['kpi'];
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-4 py-3.5">
                                        <div class="font-bold text-slate-900">{{ $user->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $user->phone }}</div>
                                    </td>
                                    <td class="px-3 py-3.5">
                                        @if($user->isAdmin())
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-50 text-purple-700">Super Admin</span>
                                        @elseif($user->isManager())
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700">Manager</span>
                                        @elseif($user->isHr())
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-pink-50 text-pink-700">HR Executive</span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">Field Staff</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3.5 text-slate-600">
                                        {{ $user->manager?->name ?? '—' }}
                                    </td>
                                    <td class="px-3 py-3.5 text-center font-semibold text-slate-800">
                                        {{ $kpi['days_present'] }} / {{ $kpi['expected_workdays'] }}
                                    </td>
                                    <td class="px-3 py-3.5 text-right font-mono text-slate-700">
                                        {{ number_format($kpi['total_regular_hours'], 1) }}h
                                    </td>
                                    <td class="px-3 py-3.5 text-right font-mono font-bold {{ $kpi['total_overtime_hours'] > 0 ? 'text-orange-600' : 'text-slate-400' }}">
                                        {{ number_format($kpi['total_overtime_hours'], 1) }}h
                                    </td>
                                    <td class="px-3 py-3.5 text-right font-mono font-bold text-slate-900">
                                        {{ number_format($kpi['total_worked_hours'], 1) }}h
                                    </td>
                                    <td class="px-3 py-3.5 text-center">
                                        @if($kpi['total_leave_days'] > 0)
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-sky-50 text-sky-700">
                                                {{ $kpi['total_leave_days'] }}d ({{ $kpi['annual_leave_days'] }} AL / {{ $kpi['medical_leave_days'] }} MC)
                                            </span>
                                        @else
                                            <span class="text-slate-400 font-mono">0</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3.5 text-center">
                                        @if($kpi['late_count'] > 0 || $kpi['incomplete_count'] > 0)
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700">
                                                {{ $kpi['late_count'] }} Late / {{ $kpi['incomplete_count'] }} Incomp.
                                            </span>
                                        @else
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold text-emerald-600">✓ None</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <a href="{{ route('attendance.monthly.slip', ['user' => $user->id, 'month' => $month, 'year' => $year]) }}" 
                                           target="_blank"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Timesheet Slip
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-slate-400">
                                        No active staff members found matching the specified filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-400 mt-8">
        &copy; {{ date('Y') }} Tumpat Solutions Sdn Bhd &bull; Tap & Track Operations & Attendance Engine
    </footer>
</body>
</html>
