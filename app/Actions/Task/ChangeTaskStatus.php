<?php

namespace App\Actions\Task;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskReviewed;
use App\Notifications\TaskSubmitted;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ChangeTaskStatus
{
    public function execute(Task $task, string $to, User $actor, ?string $note = null): Task
    {
        $from = $task->status;

        if (! $task->canTransitionTo($to)) {
            throw new RuntimeException("Cannot move task from {$from} to {$to}.");
        }

        return DB::transaction(function () use ($task, $from, $to, $actor, $note) {
            $attributes = ['status' => $to];

            if ($to === 'submitted') {
                $attributes['submitted_at'] = now();
            }

            if (in_array($to, ['approved', 'rejected'], true)) {
                $attributes['reviewed_by'] = $actor->id;
                $attributes['reviewed_at'] = now();
                $attributes['review_notes'] = $note;
            }

            $task->update($attributes);

            $task->histories()->create([
                'user_id' => $actor->id,
                'action' => 'status_changed',
                'from_status' => $from,
                'to_status' => $to,
                'note' => $note,
            ]);

            activity()->causedBy($actor)->performedOn($task)
                ->log("Changed task '{$task->title}' status from {$from} to {$to}");

            $this->notify($task->fresh(['assignee', 'assigner']), $to);

            return $task;
        });
    }

    private function notify(Task $task, string $to): void
    {
        if ($to === 'submitted') {
            $task->assigner?->notify(new TaskSubmitted($task));
        }

        if (in_array($to, ['approved', 'rejected'], true)) {
            $task->assignee?->notify(new TaskReviewed($task));
        }
    }
}
