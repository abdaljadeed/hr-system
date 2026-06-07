<?php

namespace App\Services\Dashboard;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class DashboardService
{
    public function getStats(): array
    {
        $user = $this->user();
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $yesterday = $now->copy()->subDay()->toDateString();

        return [
            'total_employees' => Employee::accessibleBy($user)
                ->where('employment_status', 'active')
                ->count(),
            'total_employees_prev' => Employee::accessibleBy($user)
                ->where('employment_status', 'active')
                ->where('created_at', '<', $monthStart)
                ->count(),
            'present_today' => Attendance::accessibleBy($user)
                ->forDate($now->toDateString())
                ->whereIn('status', ['present', 'late'])
                ->count(),
            'present_today_prev' => Attendance::accessibleBy($user)
                ->forDate($yesterday)
                ->whereIn('status', ['present', 'late'])
                ->count(),
            'pending_leaves' => LeaveRequest::accessibleBy($user)
                ->where('status', 'pending')
                ->count(),
            'pending_leaves_prev' => LeaveRequest::accessibleBy($user)
                ->where('status', 'pending')
                ->where('created_at', '<', $monthStart)
                ->count(),
            'open_tasks' => Task::accessibleBy($user)
                ->whereIn('status', ['todo', 'in_progress'])
                ->count(),
            'open_tasks_prev' => Task::accessibleBy($user)
                ->whereIn('status', ['todo', 'in_progress'])
                ->where('created_at', '<', $monthStart)
                ->count(),
            'this_month_payroll_total' => (float) Payroll::accessibleBy($user)
                ->forMonth((int) $now->year, (int) $now->month)
                ->whereIn('status', ['finalized', 'paid'])
                ->sum('net_salary'),
        ];
    }

    public function getAttendanceTrend(int $days = 30): array
    {
        $user = $this->user();
        $start = today()->subDays($days - 1);

        $records = Attendance::accessibleBy($user)
            ->whereBetween('date', [$start->toDateString(), today()->toDateString()])
            ->get(['date', 'status']);

        $byDate = $records->groupBy(fn ($record) => $record->date->toDateString());

        $labels = [];
        $present = [];
        $absent = [];

        foreach (CarbonPeriod::create($start, today()) as $date) {
            $dayRecords = $byDate->get($date->toDateString(), collect());

            $labels[] = $date->format('M j');
            $present[] = $dayRecords->whereIn('status', ['present', 'late'])->count();
            $absent[] = $dayRecords->where('status', 'absent')->count();
        }

        return [
            'labels' => $labels,
            'present' => $present,
            'absent' => $absent,
        ];
    }

    public function getTasksByStatus(): Collection
    {
        $counts = Task::accessibleBy($this->user())->get(['status'])->countBy('status');

        return collect(Task::STATUSES)->mapWithKeys(fn ($status) => [
            $status => $counts->get($status, 0),
        ]);
    }

    public function getLeavesByType(): Collection
    {
        $now = now();

        return LeaveRequest::accessibleBy($this->user())
            ->where('status', 'approved')
            ->whereYear('start_date', $now->year)
            ->whereMonth('start_date', $now->month)
            ->with('leaveType')
            ->get()
            ->groupBy('leave_type_id')
            ->map(fn ($group) => [
                'name' => $group->first()->leaveType->name,
                'color' => $group->first()->leaveType->color,
                'days' => (float) $group->sum('days'),
            ])
            ->values();
    }

    public function getTopEmployees(int $limit = 5): Collection
    {
        $now = now();

        $counts = Task::accessibleBy($this->user())
            ->where('status', 'approved')
            ->whereYear('reviewed_at', $now->year)
            ->whereMonth('reviewed_at', $now->month)
            ->get(['assigned_to'])
            ->countBy('assigned_to')
            ->sortDesc()
            ->take($limit);

        if ($counts->isEmpty()) {
            return collect();
        }

        $users = User::with('employee.department')
            ->whereIn('id', $counts->keys())
            ->get()
            ->keyBy('id');

        return $counts->map(fn ($count, $userId) => [
            'name' => $users[$userId]?->employee?->full_name ?? $users[$userId]?->name ?? '—',
            'department' => $users[$userId]?->employee?->department?->name ?? '—',
            'count' => $count,
        ])->values();
    }

    public function getBirthdaysThisMonth(): Collection
    {
        $now = now();

        return Employee::accessibleBy($this->user())
            ->where('employment_status', 'active')
            ->whereNotNull('date_of_birth')
            ->whereMonth('date_of_birth', $now->month)
            ->get()
            ->map(function ($employee) use ($now) {
                $birthday = Carbon::create($now->year, $employee->date_of_birth->month, $employee->date_of_birth->day)->startOfDay();

                return [
                    'name' => $employee->full_name,
                    'date' => $employee->date_of_birth->format('M j'),
                    'day' => (int) $employee->date_of_birth->day,
                    'days_until' => (int) $now->copy()->startOfDay()->diffInDays($birthday, false),
                ];
            })
            ->sortBy('day')
            ->values();
    }

    public function getAnniversariesThisMonth(): Collection
    {
        $now = now();

        return Employee::accessibleBy($this->user())
            ->where('employment_status', 'active')
            ->whereNotNull('hire_date')
            ->whereMonth('hire_date', $now->month)
            ->get()
            ->map(fn ($employee) => [
                'name' => $employee->full_name,
                'date' => $employee->hire_date->format('M j'),
                'day' => (int) $employee->hire_date->day,
                'years' => max(0, (int) $now->year - (int) $employee->hire_date->year),
            ])
            ->filter(fn ($row) => $row['years'] > 0)
            ->sortBy('day')
            ->values();
    }

    private function user(): User
    {
        return auth()->user();
    }
}
