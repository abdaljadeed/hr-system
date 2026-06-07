<?php

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Employee Name', 'Department', 'Date', 'Check In', 'Check Out', 'Worked Hours', 'Status'];
    }

    public function map($row): array
    {
        /** @var Attendance $row */
        return [
            $row->employee->full_name,
            $row->employee->department?->name ?? '—',
            $row->date->format('Y-m-d'),
            $row->check_in?->format('H:i') ?? '—',
            $row->check_out?->format('H:i') ?? '—',
            $row->worked_hours !== null ? (float) $row->worked_hours : '—',
            ucwords(str_replace('_', ' ', $row->status)),
        ];
    }
}
