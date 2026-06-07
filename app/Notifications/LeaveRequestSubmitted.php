<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmitted extends Notification
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
            'type' => 'leave.submitted',
            'leave_request_id' => $this->leaveRequest->id,
            'employee' => $this->leaveRequest->employee->full_name,
            'leave_type' => $this->leaveRequest->leaveType->name,
            'days' => (float) $this->leaveRequest->days,
            'start_date' => $this->leaveRequest->start_date->toDateString(),
            'end_date' => $this->leaveRequest->end_date->toDateString(),
            'message' => "{$this->leaveRequest->employee->full_name} requested {$this->leaveRequest->days} day(s) of {$this->leaveRequest->leaveType->name} leave.",
            'url' => route('leaves.show', $this->leaveRequest),
        ];
    }
}
