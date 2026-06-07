<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; background: #fff; }
    .page { padding: 40px; max-width: 700px; margin: 0 auto; }
    .header { border-bottom: 2px solid #1f2937; padding-bottom: 16px; margin-bottom: 24px; }
    .header h1 { font-size: 22px; font-weight: bold; color: #1f2937; }
    .header .subtitle { font-size: 12px; color: #6b7280; margin-top: 4px; }
    .section { margin-bottom: 24px; }
    .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; margin-bottom: 12px; }
    .info-grid { display: table; width: 100%; }
    .info-row { display: table-row; }
    .info-label { display: table-cell; width: 40%; font-size: 11px; color: #6b7280; padding: 4px 0; }
    .info-value { display: table-cell; font-size: 12px; color: #1f2937; font-weight: 500; padding: 4px 0; }
    .stat-grid { width: 100%; border-collapse: collapse; }
    .stat-grid td { padding: 6px 12px; text-align: center; }
    .stat-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; }
    .stat-num { font-size: 20px; font-weight: bold; color: #1f2937; }
    .stat-lbl { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
    .items-table { width: 100%; border-collapse: collapse; }
    .items-table th { background: #f9fafb; text-align: left; padding: 8px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
    .items-table td { padding: 8px 10px; border-bottom: 1px solid #f3f4f6; }
    .items-table tr.base td { border-bottom: 1px solid #e5e7eb; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 10px; font-weight: 600; }
    .badge-bonus { background: #d1fae5; color: #065f46; }
    .badge-deduction { background: #fee2e2; color: #991b1b; }
    .badge-earning { background: #dbeafe; color: #1e40af; }
    .amount-bonus { color: #065f46; }
    .amount-deduction { color: #991b1b; }
    .net-row td { padding-top: 12px; border-top: 2px solid #1f2937; font-weight: bold; font-size: 14px; }
    .footer { margin-top: 40px; border-top: 1px solid #e5e7eb; padding-top: 16px; font-size: 10px; color: #9ca3af; }
</style>
</head>
<body>
<div class="page">
    <div class="header">
        <h1>{{ config('app.name', 'HR System') }}</h1>
        <div class="subtitle">Payslip — {{ $payroll->period_label }}</div>
    </div>

    <div class="section">
        <div class="section-title">Employee Details</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Employee Name</div>
                <div class="info-value">{{ $payroll->employee->full_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Employee Code</div>
                <div class="info-value">{{ $payroll->employee->employee_code }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Department</div>
                <div class="info-value">{{ $payroll->employee->department?->name ?? '—' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Job Title</div>
                <div class="info-value">{{ $payroll->employee->job_title ?? '—' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Pay Period</div>
                <div class="info-value">{{ $payroll->period_label }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Attendance Summary</div>
        <table class="stat-grid">
            <tr>
                <td><div class="stat-box"><div class="stat-num">{{ $payroll->worked_days }}</div><div class="stat-lbl">Worked</div></div></td>
                <td><div class="stat-box"><div class="stat-num">{{ $payroll->absent_days }}</div><div class="stat-lbl">Absent</div></div></td>
                <td><div class="stat-box"><div class="stat-num">{{ $payroll->unpaid_leave_days }}</div><div class="stat-lbl">Unpaid Leave</div></div></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Earnings &amp; Deductions</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Type</th>
                    <th style="text-align:right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr class="base">
                    <td>Base Salary</td>
                    <td><span class="badge badge-earning">Earning</span></td>
                    <td style="text-align:right" class="amount-bonus">{{ number_format((float)$payroll->base_salary, 2) }}</td>
                </tr>
                @foreach($payroll->items as $item)
                    <tr>
                        <td>{{ $item->label }}</td>
                        <td>
                            @if($item->type === 'bonus')
                                <span class="badge badge-bonus">Bonus</span>
                            @else
                                <span class="badge badge-deduction">Deduction</span>
                            @endif
                        </td>
                        <td style="text-align:right" class="{{ $item->type === 'bonus' ? 'amount-bonus' : 'amount-deduction' }}">
                            {{ $item->type === 'bonus' ? '+' : '-' }}{{ number_format((float)$item->amount, 2) }}
                        </td>
                    </tr>
                @endforeach
                <tr class="net-row">
                    <td colspan="2">Net Salary</td>
                    <td style="text-align:right">{{ $payroll->net_salary_formatted }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        Generated on {{ now()->format('d M Y, H:i') }} by {{ $payroll->generatedBy?->name ?? 'System' }}
        &nbsp;·&nbsp; Status: {{ ucfirst($payroll->status) }}
    </div>
</div>
</body>
</html>
