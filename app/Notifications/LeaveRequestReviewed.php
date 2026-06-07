<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestReviewed extends Notification
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'leave.reviewed',
            'leave_request_id' => $this->leaveRequest->id,
            'status' => $this->leaveRequest->status,
            'leave_type' => $this->leaveRequest->leaveType->name,
            'days' => (float) $this->leaveRequest->days,
            'start_date' => $this->leaveRequest->start_date->toDateString(),
            'end_date' => $this->leaveRequest->end_date->toDateString(),
            'message' => "Your {$this->leaveRequest->leaveType->name} leave request was {$this->leaveRequest->status}.",
            'url' => route('leaves.show', $this->leaveRequest),
        ];
    }
}
