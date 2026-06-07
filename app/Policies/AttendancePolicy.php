<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('attendance.view');
    }

    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->can('attendance.manage')) {
            return true;
        }

        if ($user->hasRole('Team Lead')) {
            $deptId = $user->employee?->department_id;

            return $deptId && $attendance->employee->department_id === $deptId;
        }

        return $user->employee?->id === $attendance->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->can('attendance.manage');
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $user->can('attendance.manage');
    }

    public function checkIn(User $user, Employee $employee): bool
    {
        return $user->employee?->id === $employee->id
            || $user->can('attendance.manage');
    }

    public function checkOut(User $user, Employee $employee): bool
    {
        return $user->employee?->id === $employee->id
            || $user->can('attendance.manage');
    }
}
