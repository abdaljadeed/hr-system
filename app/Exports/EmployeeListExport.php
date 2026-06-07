<?php

namespace App\Exports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeeListExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Code', 'Full Name', 'Department', 'Job Title', 'Base Salary', 'Hire Date', 'Employment Status'];
    }

    public function map($row): array
    {
        /** @var Employee $row */
        return [
            $row->employee_code,
            $row->full_name,
            $row->department?->name ?? '—',
            $row->job_title ?? '—',
            (float) $row->base_salary,
            $row->hire_date?->format('Y-m-d') ?? '—',
            ucfirst($row->employment_status),
        ];
    }
}
