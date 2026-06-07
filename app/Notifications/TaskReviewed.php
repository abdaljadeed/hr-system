<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskReviewed extends Notification
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
            'type' => 'task.reviewed',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'status' => $this->task->status,
            'review_notes' => $this->task->review_notes,
            'message' => "Your task \"{$this->task->title}\" was {$this->task->status}.",
            'url' => route('tasks.show', $this->task),
        ];
    }
}
