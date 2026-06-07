<?php

namespace App\Services\Report;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;

class ReportService
{
    public function attendance(User $user, array $filters): Collection
    {
        [$year, $month] = $this->period($filters);

        return Attendance::accessibleBy($user)
            ->with('employee.department')
            ->forMonth($year, $month)
            ->when($filters['employee_id'] ?? null, fn ($q, $v) => $q->forEmployee($v))
            ->orderBy('date')
            ->get();
    }

    public function payroll(User $user, array $filters): Collection
    {
        [$year, $month] = $this->period($filters);

        return Payroll::accessibleBy($user)
            ->with('employee.department')
            ->forMonth($year, $month)
            ->get();
    }

    public function employees(User $user, array $filters): Collection
    {
        return Employee::accessibleBy($user)
            ->with('department')
            ->filter([
                'department_id' => $filters['department_id'] ?? null,
                'status' => $filters['status'] ?? null,
            ])
            ->orderBy('first_name')
            ->get();
    }

    public function leaves(User $user, array $filters): Collection
    {
        return LeaveRequest::accessibleBy($user)
            ->with(['employee', 'leaveType', 'reviewer'])
            ->when($filters['employee_id'] ?? null, fn ($q, $v) => $q->forEmployee($v))
            ->when($filters['leave_type_id'] ?? null, fn ($q, $v) => $q->where('leave_type_id', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when(($filters['year'] ?? null) && ($filters['month'] ?? null), function ($q) use ($filters) {
                $q->whereYear('start_date', $filters['year'])->whereMonth('start_date', $filters['month']);
            })
            ->orderByDesc('start_date')
            ->get();
    }

    public function performance(User $user, array $filters): array
    {
        [$year, $month] = $this->period($filters);

        $employee = Employee::accessibleBy($user)
            ->with('department')
            ->findOrFail($filters['employee_id']);

        $attendance = Attendance::forEmployee($employee->id)->forMonth($year, $month)->get();

        $attendanceSummary = [
            'present' => $attendance->where('status', 'present')->count(),
            'late' => $attendance->where('status', 'late')->count(),
            'absent' => $attendance->where('status', 'absent')->count(),
            'on_leave' => $attendance->where('status', 'on_leave')->count(),
            'worked_hours' => (float) $attendance->sum('worked_hours'),
        ];

        $leaves = LeaveRequest::forEmployee($employee->id)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->with('leaveType')
            ->get();

        $leaveSummary = [
            'requests' => $leaves->count(),
            'days' => (float) $leaves->sum('days'),
        ];

        $taskSummary = ['approved' => 0, 'submitted' => 0, 'open' => 0];

        if ($employee->user_id) {
            $taskSummary['approved'] = Task::where('assigned_to', $employee->user_id)
                ->where('status', 'approved')
                ->whereYear('reviewed_at', $year)
                ->whereMonth('reviewed_at', $month)
                ->count();

            $taskSummary['submitted'] = Task::where('assigned_to', $employee->user_id)
                ->whereYear('submitted_at', $year)
                ->whereMonth('submitted_at', $month)
                ->count();

            $taskSummary['open'] = Task::where('assigned_to', $employee->user_id)
                ->whereIn('status', ['todo', 'in_progress'])
                ->count();
        }

        return compact('employee', 'attendanceSummary', 'leaveSummary', 'leaves', 'taskSummary', 'year', 'month');
    }

    private function period(array $filters): array
    {
        return [
            (int) ($filters['year'] ?? now()->year),
            (int) ($filters['month'] ?? now()->month),
        ];
    }
}
