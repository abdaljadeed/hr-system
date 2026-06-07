<?php

namespace App\Services\Task;

use App\Actions\Task\ChangeTaskStatus;
use App\Actions\Task\ReassignTask;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function __construct(
        private ChangeTaskStatus $changeTaskStatus,
        private ReassignTask $reassignTask,
    ) {}

    public function create(array $data, User $creator): Task
    {
        return DB::transaction(function () use ($data, $creator) {
            $task = Task::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'assigned_to' => $data['assigned_to'],
                'assigned_by' => $creator->id,
                'priority' => $data['priority'] ?? 'medium',
                'due_date' => $data['due_date'] ?? null,
                'status' => 'todo',
            ]);

            $task->histories()->create([
                'user_id' => $creator->id,
                'action' => 'created',
                'to_status' => 'todo',
            ]);

            activity()->causedBy($creator)->performedOn($task)
                ->log("Created task '{$task->title}'");

            $task->assignee?->notify(new TaskAssigned($task));

            return $task;
        });
    }

    public function update(Task $task, array $data): Task
    {
        $task->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'] ?? $task->priority,
            'due_date' => $data['due_date'] ?? null,
        ]);

        return $task;
    }

    public function changeStatus(Task $task, string $to, User $actor, ?string $note = null): Task
    {
        return $this->changeTaskStatus->execute($task, $to, $actor, $note);
    }

    public function reassign(Task $task, int $newAssigneeId, User $actor): Task
    {
        return $this->reassignTask->execute($task, $newAssigneeId, $actor);
    }

    public function addComment(Task $task, User $user, string $body): TaskComment
    {
        return $task->comments()->create([
            'user_id' => $user->id,
            'body' => $body,
        ]);
    }
}
