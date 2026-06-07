<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification
{
    use Queueable;

    public function __construct(public Task $task) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task.assigned',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'priority' => $this->task->priority,
            'due_date' => $this->task->due_date?->toDateString(),
            'message' => "You were assigned the task \"{$this->task->title}\".",
            'url' => route('tasks.show', $this->task),
        ];
    }
}
