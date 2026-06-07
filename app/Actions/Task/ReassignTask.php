<?php

namespace App\Actions\Task;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReassignTask
{
    public function execute(Task $task, int $newAssigneeId, User $actor): Task
    {
        if ($task->assigned_to === $newAssigneeId) {
            throw new RuntimeException('Task is already assigned to this user.');
        }

        $newAssignee = User::findOrFail($newAssigneeId);

        return DB::transaction(function () use ($task, $newAssignee, $actor) {
            $previous = $task->assignee;

            $task->update(['assigned_to' => $newAssignee->id]);

            $task->histories()->create([
                'user_id' => $actor->id,
                'action' => 'reassigned',
                'note' => "From {$previous?->name} to {$newAssignee->name}",
            ]);

            $assigneeName = $newAssignee->employee?->full_name ?? $newAssignee->name;
            activity()->causedBy($actor)->performedOn($task)
                ->log("Reassigned task '{$task->title}' to {$assigneeName}");

            $newAssignee->notify(new TaskAssigned($task->fresh()));

            return $task;
        });
    }
}
