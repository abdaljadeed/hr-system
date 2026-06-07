<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leaves.view');
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->can('leaves.approve')) {
            return $this->withinScope($user, $leaveRequest);
        }

        return $user->employee?->id === $leaveRequest->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->can('leaves.request') && $user->employee !== null;
    }

    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->can('leaves.approve')
            && $leaveRequest->isPending()
            && $this->withinScope($user, $leaveRequest);
    }

    public function cancel(User $user, LeaveRequest $leaveRequest): bool
    {
        return $leaveRequest->isPending()
            && $user->employee?->id === $leaveRequest->employee_id;
    }

    private function withinScope(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->hasRole(['Admin', 'HR Manager'])) {
            return true;
        }

        if ($user->hasRole('Team Lead')) {
            $deptId = $user->employee?->department_id;

            return $deptId && $leaveRequest->employee->department_id === $deptId;
        }

        return false;
    }
}
