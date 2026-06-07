<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tasks.view');
    }

    public function view(User $user, Task $task): bool
    {
        return $this->withinScope($user, $task);
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.manage');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->can('tasks.manage')
            && $task->status !== 'approved'
            && $this->withinScope($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->can('tasks.manage') && $this->withinScope($user, $task);
    }

    public function start(User $user, Task $task): bool
    {
        return $user->id === $task->assigned_to
            && in_array($task->status, ['todo', 'rejected'], true);
    }

    public function submit(User $user, Task $task): bool
    {
        return $user->id === $task->assigned_to && $task->status === 'in_progress';
    }

    public function review(User $user, Task $task): bool
    {
        return $user->can('tasks.manage')
            && $task->status === 'submitted'
            && $this->withinScope($user, $task);
    }

    public function reassign(User $user, Task $task): bool
    {
        return $user->can('tasks.assign') && $this->withinScope($user, $task);
    }

    public function comment(User $user, Task $task): bool
    {
        return $this->withinScope($user, $task);
    }

    private function withinScope(User $user, Task $task): bool
    {
        if ($user->hasRole(['Admin', 'HR Manager'])) {
            return true;
        }

        if ($user->id === $task->assigned_to || $user->id === $task->assigned_by) {
            return true;
        }

        if ($user->hasRole('Team Lead')) {
            $deptId = $user->employee?->department_id;

            if (! $deptId) {
                return false;
            }

            return Employee::where('department_id', $deptId)
                ->where('user_id', $task->assigned_to)
                ->exists();
        }

        return false;
    }
}
