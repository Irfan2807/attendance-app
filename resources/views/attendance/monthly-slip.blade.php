<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if(!$is_bundle)
            Monthly Timesheet - {{ $timesheet['user']->name }} ({{ $timesheet['month_name'] }})
        @else
            Monthly Payroll Timesheet Bundle - {{ $month_name }}
        @endif
    </title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|space-grotesk:500,600,700" rel="stylesheet" />

    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #0f172a;
            background: #f1f5f9;
            font-size: 11px;
            line-height: 1.35;
        }

        .no-print-bar {
            background: #1e293b;
            color: #fff;
            padding: 12px 24px;
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .no-print-bar h2 {
            font-size: 14px;
            font-weight: 600;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.15s;
        }

        .btn-orange {
            background: #F27E26;
            color: #fff;
        }

        .btn-orange:hover {
            background: #d66d1f;
        }

        .btn-gray {
            background: #334155;
            color: #f8fafc;
        }

        .btn-gray:hover {
            background: #475569;
        }

        .slip-page {
            background: #fff;
            max-width: 210mm;
            margin: 16px auto;
            padding: 16mm 16mm 14mm 16mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            border-radius: 4px;
        }

        /* Print Specifics */
        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }

            .no-print-bar {
                display: none !important;
            }

            .slip-page {
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .page-break {
                page-break-after: always !important;
                break-after: page !important;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }

        /* Header */
        .company-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .company-name {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .company-sub {
            font-size: 9.5px;
            color: #64748b;
            margin-top: 1px;
        }

        .doc-title-block {
            text-align: right;
        }

        .doc-title {
            font-size: 13px;
            font-weight: 800;
            color: #F27E26;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .doc-ref {
            font-size: 9px;
            color: #64748b;
            font-family: monospace;
            margin-top: 2px;
        }

        /* Metadata Grid */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 10px;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            font-weight: 600;
        }

        .meta-value {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 1px;
        }

        /* KPI Ribbon */
        .kpi-ribbon {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 6px;
            margin-bottom: 12px;
        }

        .kpi-card {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 5px 6px;
            text-align: center;
            background: #ffffff;
        }

        .kpi-title {
            font-size: 8px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }

        .kpi-num {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 1px;
            font-family: monospace;
        }

        .kpi-num.highlight {
            color: #F27E26;
        }

        /* Table */
        table.timesheet-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-bottom: 14px;
        }

        table.timesheet-table th, 
        table.timesheet-table td {
            border: 1px solid #cbd5e1;
            padding: 3.5px 5px;
            text-align: left;
        }

        table.timesheet-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8.5px;
            letter-spacing: 0.03em;
        }

        table.timesheet-table tr.weekend {
            background-color: #f8fafc;
            color: #94a3b8;
        }

        table.timesheet-table tr.holiday {
            background-color: #fefce8;
        }

        table.timesheet-table tr.leave {
            background-color: #f0f9ff;
        }

        .tag {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .tag-present { background: #dcfce7; color: #15803d; }
        .tag-late { background: #fef3c7; color: #b45309; }
        .tag-leave { background: #e0f2fe; color: #0369a1; }
        .tag-holiday { background: #fef9c3; color: #854d0e; }
        .tag-weekend { background: #f1f5f9; color: #64748b; }
        .tag-absent { background: #fee2e2; color: #991b1b; }

        /* Signatures */
        .signatures-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            border-top: 1px solid #cbd5e1;
            padding-top: 10px;
            margin-top: auto;
        }

        .sig-box {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sig-statement {
            font-size: 8.5px;
            color: #475569;
            margin-bottom: 25px;
            line-height: 1.3;
        }

        .sig-line {
            border-bottom: 1px solid #64748b;
            margin-bottom: 4px;
            width: 85%;
        }

        .sig-name {
            font-size: 9.5px;
            font-weight: 700;
            color: #0f172a;
        }

        .sig-title {
            font-size: 8.5px;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- Print Control Bar (Hidden during actual print) -->
    <div class="no-print-bar">
        <div>
            <h2>
                @if(!$is_bundle)
                    Timesheet Preview: {{ $timesheet['user']->name }} &bull; {{ $timesheet['month_name'] }}
                @else
                    Payroll Bundle Preview: All Staff ({{ count($bundle) }} Employees) &bull; {{ $month_name }}
                @endif
            </h2>
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-orange">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print / Save as PDF
            </button>
            <a href="{{ route('attendance.monthly.index', ['month' => $month, 'year' => $year]) }}" class="btn btn-gray">
                Back to Hub
            </a>
        </div>
    </div>

    @php
        $sheets = !$is_bundle ? [$timesheet] : $bundle;
    @endphp

    @foreach($sheets as $index => $item)
        @php
            $user = $item['user'];
            $kpi = $item['kpi'];
            $days = $item['days'];
            $refId = 'TTS-' . $item['year'] . str_pad($item['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);
            $roleLabel = match($user->roleValue()) {
                1 => 'Super Admin',
                2 => 'Operations Manager',
                3 => 'Field Engineer / Staff',
                4 => 'HR Executive',
                default => 'Staff',
            };
        @endphp

        <div class="slip-page {{ $loop->last ? '' : 'page-break' }}">
            <!-- Letterhead -->
            <div class="company-header">
                <div>
                    <div class="company-name">TUMPAT SOLUTIONS SDN BHD</div>
                    <div class="company-sub">Engineering Field Operations & Site Services &bull; Reg: 202401089201</div>
                </div>
                <div class="doc-title-block">
                    <div class="doc-title">Monthly Attendance Slip</div>
                    <div class="doc-ref">REF: {{ $refId }} &bull; PAYROLL CYCLE</div>
                </div>
            </div>

            <!-- Employee & Period Info -->
            <div class="meta-grid">
                <div class="meta-item">
                    <span class="meta-label">Employee Name</span>
                    <span class="meta-value">{{ $user->name }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Designation / Role</span>
                    <span class="meta-value">{{ $roleLabel }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Supervisor / Manager</span>
                    <span class="meta-value">{{ $user->manager?->name ?? 'Direct Management' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Pay Period</span>
                    <span class="meta-value">{{ $item['month_name'] }}</span>
                </div>
            </div>

            <!-- Summary KPI Ribbon -->
            <div class="kpi-ribbon">
                <div class="kpi-card">
                    <div class="kpi-title">Worked Days</div>
                    <div class="kpi-num">{{ $kpi['days_present'] }} / {{ $kpi['expected_workdays'] }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Regular Hours</div>
                    <div class="kpi-num">{{ number_format($kpi['total_regular_hours'], 1) }}h</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Overtime (OT)</div>
                    <div class="kpi-num highlight">{{ number_format($kpi['total_overtime_hours'], 1) }}h</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Total Hours</div>
                    <div class="kpi-num">{{ number_format($kpi['total_worked_hours'], 1) }}h</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Leaves (Days)</div>
                    <div class="kpi-num">{{ $kpi['total_leave_days'] }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-title">Late Starts</div>
                    <div class="kpi-num">{{ $kpi['late_count'] }}</div>
                </div>
            </div>

            <!-- 30/31 Day Calendar Breakdown -->
            <table class="timesheet-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">Date</th>
                        <th style="width: 32px;">Day</th>
                        <th>Work Location / Site</th>
                        <th style="width: 45px; text-align: center;">In</th>
                        <th style="width: 45px; text-align: center;">Out</th>
                        <th style="width: 50px; text-align: right;">Regular</th>
                        <th style="width: 45px; text-align: right;">OT</th>
                        <th style="width: 110px;">Status / Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($days as $day)
                        @php
                            $rowClass = '';
                            if ($day['is_weekend']) $rowClass = 'weekend';
                            elseif ($day['is_holiday']) $rowClass = 'holiday';
                            elseif ($day['leave']) $rowClass = 'leave';
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td style="font-weight: 700; font-family: monospace;">{{ str_pad($day['day'], 2, '0', STR_PAD_LEFT) }}/{{ str_pad($item['month'], 2, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $day['day_name'] }}</td>
                            <td>{{ $day['site_name'] ?? ($day['is_holiday'] ? $day['holiday_name'] : ($day['is_weekend'] ? 'Rest Day' : '—')) }}</td>
                            <td style="text-align: center; font-family: monospace;">{{ $day['clock_in'] ?? '—' }}</td>
                            <td style="text-align: center; font-family: monospace;">{{ $day['clock_out'] ?? '—' }}</td>
                            <td style="text-align: right; font-family: monospace;">
                                {{ $day['regular_minutes'] > 0 ? number_format($day['regular_minutes'] / 60, 1) . 'h' : '—' }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-weight: {{ $day['overtime_minutes'] > 0 ? '700' : 'normal' }}; color: {{ $day['overtime_minutes'] > 0 ? '#b45309' : 'inherit' }};">
                                {{ $day['overtime_minutes'] > 0 ? number_format($day['overtime_minutes'] / 60, 1) . 'h' : '—' }}
                            </td>
                            <td>
                                @if($day['attendance'])
                                    @if(str_contains($day['status'], 'late'))
                                        <span class="tag tag-late">{{ $day['status_label'] }}</span>
                                    @elseif(str_contains($day['status'], 'incomplete'))
                                        <span class="tag tag-late">Incomplete</span>
                                    @else
                                        <span class="tag tag-present">Present</span>
                                    @endif
                                @elseif($day['leave'])
                                    <span class="tag tag-leave">{{ $day['status_label'] }}</span>
                                @elseif($day['is_holiday'])
                                    <span class="tag tag-holiday">Public Holiday</span>
                                @elseif($day['is_weekend'])
                                    <span class="tag tag-weekend">Weekend</span>
                                @else
                                    <span class="tag tag-absent">Absent / Unpaid</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Formal Signatures & Verification -->
            <div class="signatures-container">
                <div class="sig-box">
                    <p class="sig-statement">
                        <strong>Employee Declaration:</strong><br>
                        I certify that the attendance times, overtime, and work locations shown above accurately reflect my working hours for this period.
                    </p>
                    <div>
                        <div class="sig-line"></div>
                        <div class="sig-name">{{ $user->name }}</div>
                        <div class="sig-title">Employee Signature &bull; Date: _____ / _____ / {{ $item['year'] }}</div>
                    </div>
                </div>

                <div class="sig-box">
                    <p class="sig-statement">
                        <strong>HR / Management Certification:</strong><br>
                        Audited and verified against geofence and supervisor logs. Approved for monthly salary disbursement and statutory records.
                    </p>
                    <div>
                        <div class="sig-line"></div>
                        <div class="sig-name">{{ $user->manager?->name ?? 'HR Executive / Operations' }}</div>
                        <div class="sig-title">Authorized Approval & Stamp &bull; Date: _____ / _____ / {{ $item['year'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

</body>
</html>

