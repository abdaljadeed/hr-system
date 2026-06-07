<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; background: #fff; }
    .page { padding: 32px; }
    .header { border-bottom: 2px solid #1f2937; padding-bottom: 14px; margin-bottom: 18px; }
    .header h1 { font-size: 20px; font-weight: bold; }
    .header .subtitle { font-size: 12px; color: #6b7280; margin-top: 4px; }
    .meta { font-size: 10px; color: #6b7280; margin-bottom: 16px; }
    .meta strong { color: #374151; }
    table { width: 100%; border-collapse: collapse; }
    thead th { background: #f3f4f6; text-align: left; padding: 7px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.04em; color: #6b7280; border-bottom: 1px solid #d1d5db; }
    thead th.text-right { text-align: right; }
    tbody td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; }
    tbody tr:nth-child(even) { background: #fafafa; }
    .text-right { text-align: right; }
    tfoot td { padding: 8px; border-top: 2px solid #1f2937; font-weight: bold; font-size: 12px; }
    .footer { margin-top: 24px; border-top: 1px solid #e5e7eb; padding-top: 12px; font-size: 9px; color: #9ca3af; }
    .empty { padding: 24px; text-align: center; color: #9ca3af; }
</style>
</head>
<body>
<div class="page">
    <div class="header">
        <h1>{{ config('app.name', 'HR System') }}</h1>
        <div class="subtitle">Payroll Report — {{ $period }}</div>
    </div>

    <div class="meta">
        <strong>Scope:</strong> {{ $filterSummary }} &nbsp;·&nbsp;
        <strong>Payslips:</strong> {{ $rows->count() }} &nbsp;·&nbsp;
        <strong>Generated:</strong> {{ now()->format('d M Y, H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th class="text-right">Base</th>
                <th class="text-right">Worked</th>
                <th class="text-right">Absent</th>
                <th class="text-right">Bonuses</th>
                <th class="text-right">Deductions</th>
                <th class="text-right">Net Salary</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->employee->full_name }}</td>
                    <td>{{ $row->employee->department?->name ?? '—' }}</td>
                    <td class="text-right">{{ number_format((float) $row->base_salary, 2) }}</td>
                    <td class="text-right">{{ $row->worked_days }}</td>
                    <td class="text-right">{{ $row->absent_days }}</td>
                    <td class="text-right">{{ number_format((float) $row->total_bonuses, 2) }}</td>
                    <td class="text-right">{{ number_format((float) $row->total_deductions, 2) }}</td>
                    <td class="text-right">{{ number_format((float) $row->net_salary, 2) }}</td>
                    <td>{{ ucfirst($row->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="empty">No payroll records for this period.</td></tr>
            @endforelse
        </tbody>
        @if($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="7">Total Payroll Cost</td>
                    <td class="text-right">{{ number_format($totalCost, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">{{ config('app.name', 'HR System') }} · Payroll Report · Confidential</div>
</div>
</body>
</html>
