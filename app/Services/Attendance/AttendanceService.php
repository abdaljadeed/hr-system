<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use RuntimeException;

class AttendanceService
{
    public function checkIn(Employee $employee): Attendance
    {
        $today = now()->toDateString();

        $existing = Attendance::forEmployee($employee->id)
            ->forDate($today)
            ->first();

        if ($existing?->check_in !== null) {
            throw new RuntimeException('Already checked in today.');
        }

        $now = now();
        $threshold = Carbon::today()->setTimeFromTimeString(config('hr.attendance.late_threshold'));
        $status = $now->gt($threshold) ? 'late' : 'present';

        return Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            ['check_in' => $now->toDateTimeString(), 'status' => $status]
        );
    }

    public function checkOut(Employee $employee): Attendance
    {
        $today = now()->toDateString();
        $attendance = Attendance::forEmployee($employee->id)
            ->forDate($today)
            ->first();

        if (! $attendance?->check_in) {
            throw new RuntimeException('No open check-in found for today.');
        }

        if ($attendance->check_out !== null) {
            throw new RuntimeException('Already checked out today.');
        }

        $now = now();
        $workedHours = $attendance->check_in->diffInMinutes($now) / 60;

        $attendance->update([
            'check_out' => $now,
            'worked_hours' => round($workedHours, 2),
        ]);

        return $attendance->fresh();
    }

    public function getMonthlyReport(Employee $employee, int $year, int $month): array
    {
        $records = Attendance::forEmployee($employee->id)
            ->forMonth($year, $month)
            ->orderBy('date')
            ->get();

        $summary = [
            'present' => $records->where('status', 'present')->count(),
            'late' => $records->where('status', 'late')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'on_leave' => $records->where('status', 'on_leave')->count(),
            'total_worked' => (float) $records->sum('worked_hours'),
        ];

        return compact('records', 'summary');
    }

    public function manualEntry(Employee $employee, array $data): Attendance
    {
        $checkIn = isset($data['check_in']) ? Carbon::parse($data['check_in']) : null;
        $checkOut = isset($data['check_out']) ? Carbon::parse($data['check_out']) : null;

        $workedHours = ($checkIn && $checkOut)
            ? round($checkIn->diffInMinutes($checkOut) / 60, 2)
            : null;

        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $data['date']],
            [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'worked_hours' => $workedHours,
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]
        );

        activity()->causedBy(auth()->user())->performedOn($attendance)
            ->log("Manual attendance entry for {$employee->full_name} on {$data['date']}");

        return $attendance;
    }
}
