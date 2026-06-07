<?php

namespace App\Exports;

use App\Models\LeaveRequest;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeaveReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Employee Name', 'Leave Type', 'Start Date', 'End Date', 'Days Count', 'Status', 'Reviewed By'];
    }

    public function map($row): array
    {
        /** @var LeaveRequest $row */
        return [
            $row->employee->full_name,
            $row->leaveType->name,
            $row->start_date->format('Y-m-d'),
            $row->end_date->format('Y-m-d'),
            (float) $row->days,
            ucfirst($row->status),
            $row->reviewer?->name ?? '—',
        ];
    }
}
