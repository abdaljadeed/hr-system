<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskSubmitted extends Notification
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
            'type' => 'task.submitted',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'submitted_by' => $this->task->assignee?->name,
            'message' => "{$this->task->assignee?->name} submitted the task \"{$this->task->title}\" for review.",
            'url' => route('tasks.show', $this->task),
        ];
    }
}
