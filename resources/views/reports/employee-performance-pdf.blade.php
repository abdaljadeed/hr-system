<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; background: #fff; }
    .page { padding: 40px; max-width: 700px; margin: 0 auto; }
    .header { border-bottom: 2px solid #1f2937; padding-bottom: 16px; margin-bottom: 24px; }
    .header h1 { font-size: 22px; font-weight: bold; }
    .header .subtitle { font-size: 12px; color: #6b7280; margin-top: 4px; }
    .section { margin-bottom: 24px; }
    .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; margin-bottom: 12px; }
    .info-grid { display: table; width: 100%; }
    .info-row { display: table-row; }
    .info-label { display: table-cell; width: 40%; font-size: 11px; color: #6b7280; padding: 4px 0; }
    .info-value { display: table-cell; font-size: 12px; font-weight: 500; padding: 4px 0; }
    .stat-grid { width: 100%; border-collapse: collapse; }
    .stat-grid td { padding: 6px; }
    .stat-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; text-align: center; padding: 10px 6px; }
    .stat-num { font-size: 20px; font-weight: bold; }
    .stat-lbl { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
    table.list { width: 100%; border-collapse: collapse; }
    table.list th { background: #f9fafb; text-align: left; padding: 6px 8px; font-size: 9px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
    table.list td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; }
    .footer { margin-top: 40px; border-top: 1px solid #e5e7eb; padding-top: 16px; font-size: 10px; color: #9ca3af; }
    .muted { color: #9ca3af; }
</style>
</head>
<body>
<div class="page">
    <div class="header">
        <h1>{{ config('app.name', 'HR System') }}</h1>
        <div class="subtitle">Employee Performance Report — {{ $period }}</div>
    </div>

    <div class="section">
        <div class="section-title">Employee</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Name</div>
                <div class="info-value">{{ $employee->full_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Employee Code</div>
                <div class="info-value">{{ $employee->employee_code }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Department</div>
                <div class="info-value">{{ $employee->department?->name ?? '—' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Job Title</div>
                <div class="info-value">{{ $employee->job_title ?? '—' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Period Covered</div>
                <div class="info-value">{{ $period }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Attendance Summary</div>
        <table class="stat-grid">
            <tr>
                <td><div class="stat-box"><div class="stat-num">{{ $attendanceSummary['present'] }}</div><div class="stat-lbl">Present</div></div></td>
                <td><div class="stat-box"><div class="stat-num">{{ $attendanceSummary['late'] }}</div><div class="stat-lbl">Late</div></div></td>
                <td><div class="stat-box"><div class="stat-num">{{ $attendanceSummary['absent'] }}</div><div class="stat-lbl">Absent</div></div></td>
                <td><div class="stat-box"><div class="stat-num">{{ $attendanceSummary['on_leave'] }}</div><div class="stat-lbl">On Leave</div></div></td>
                <td><div class="stat-box"><div class="stat-num">{{ number_format($attendanceSummary['worked_hours'], 1) }}</div><div class="stat-lbl">Hours</div></div></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Task Performance</div>
        <table class="stat-grid">
            <tr>
                <td><div class="stat-box"><div class="stat-num">{{ $taskSummary['approved'] }}</div><div class="stat-lbl">Approved</div></div></td>
                <td><div class="stat-box"><div class="stat-num">{{ $taskSummary['submitted'] }}</div><div class="stat-lbl">Submitted</div></div></td>
                <td><div class="stat-box"><div class="stat-num">{{ $taskSummary['open'] }}</div><div class="stat-lbl">Open</div></div></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Approved Leave ({{ $leaveSummary['requests'] }} request(s) · {{ number_format($leaveSummary['days'], 1) }} day(s))</div>
        <table class="list">
            <thead>
                <tr><th>Type</th><th>Start</th><th>End</th><th>Days</th></tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                    <tr>
                        <td>{{ $leave->leaveType->name }}</td>
                        <td>{{ $leave->start_date->format('d M Y') }}</td>
                        <td>{{ $leave->end_date->format('d M Y') }}</td>
                        <td>{{ number_format((float) $leave->days, 1) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">No approved leave in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">Generated {{ now()->format('d M Y, H:i') }} · {{ config('app.name', 'HR System') }} · Confidential</div>
</div>
</body>
</html>
