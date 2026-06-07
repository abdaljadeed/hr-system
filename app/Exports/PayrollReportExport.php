<?php

namespace App\Exports;

use App\Models\Payroll;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PayrollReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Employee Name', 'Department', 'Base Salary', 'Worked Days', 'Absent Days',
            'Unpaid Leave Days', 'Bonuses', 'Deductions', 'Net Salary', 'Status',
        ];
    }

    public function map($row): array
    {
        /** @var Payroll $row */
        return [
            $row->employee->full_name,
            $row->employee->department?->name ?? '—',
            (float) $row->base_salary,
            $row->worked_days,
            $row->absent_days,
            $row->unpaid_leave_days,
            (float) $row->total_bonuses,
            (float) $row->total_deductions,
            (float) $row->net_salary,
            ucfirst($row->status),
        ];
    }
}
