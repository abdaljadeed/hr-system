<?php

namespace App\Services\Leave;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Notifications\LeaveRequestReviewed;
use App\Notifications\LeaveRequestSubmitted;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

class LeaveRequestService
{
    public function request(Employee $employee, array $data): LeaveRequest
    {
        $leaveType = LeaveType::findOrFail($data['leave_type_id']);
        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        $days = $this->countWorkingDays($start, $end);

        if ($days <= 0) {
            throw new RuntimeException('The selected range contains no working days.');
        }

        $this->guardNoOverlap($employee, $start, $end);

        if ($leaveType->is_paid) {
            $balance = $this->balanceFor($employee, $leaveType, (int) $start->year);

            if ($balance->remaining_days < $days) {
                throw new RuntimeException(
                    "Insufficient balance: {$balance->remaining_days} day(s) remaining, {$days} requested."
                );
            }
        }

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'days' => $days,
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ]);

        $this->notifyReviewers($leaveRequest);

        return $leaveRequest;
    }

    public function approve(LeaveRequest $leaveRequest, User $reviewer, ?string $notes = null): LeaveRequest
    {
        $this->guardPending($leaveRequest);

        DB::transaction(function () use ($leaveRequest, $reviewer, $notes) {
            $leaveRequest->update([
                'status' => 'approved',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);

            if ($leaveRequest->leaveType->is_paid) {
                $balance = $this->balanceFor(
                    $leaveRequest->employee,
                    $leaveRequest->leaveType,
                    (int) $leaveRequest->start_date->year
                );
                $balance->increment('used_days', $leaveRequest->days);
            }

            $this->syncAttendance($leaveRequest);
        });

        activity()->causedBy($reviewer)->performedOn($leaveRequest)
            ->log("Approved leave request for {$leaveRequest->employee->full_name}");

        $this->notifyEmployee($leaveRequest->fresh(['leaveType', 'employee']));

        return $leaveRequest;
    }

    public function reject(LeaveRequest $leaveRequest, User $reviewer, ?string $notes = null): LeaveRequest
    {
        $this->guardPending($leaveRequest);

        $leaveRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        activity()->causedBy($reviewer)->performedOn($leaveRequest)
            ->log("Rejected leave request for {$leaveRequest->employee->full_name}");

        $this->notifyEmployee($leaveRequest->fresh(['leaveType', 'employee']));

        return $leaveRequest;
    }

    public function cancel(LeaveRequest $leaveRequest): LeaveRequest
    {
        $this->guardPending($leaveRequest);

        $leaveRequest->update(['status' => 'cancelled']);

        return $leaveRequest;
    }

    public function balanceFor(Employee $employee, LeaveType $leaveType, int $year): LeaveBalance
    {
        return LeaveBalance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'year' => $year,
            ],
            ['entitled_days' => $leaveType->default_days, 'used_days' => 0]
        );
    }

    private function countWorkingDays(Carbon $start, Carbon $end): int
    {
        $days = 0;

        foreach (CarbonPeriod::create($start, $end) as $date) {
            if (! $date->isWeekend()) {
                $days++;
            }
        }

        return $days;
    }

    private function guardNoOverlap(Employee $employee, Carbon $start, Carbon $end): void
    {
        $overlaps = LeaveRequest::forEmployee($employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_date', '<=', $end->toDateString())
            ->where('end_date', '>=', $start->toDateString())
            ->exists();

        if ($overlaps) {
            throw new RuntimeException('This range overlaps an existing leave request.');
        }
    }

    private function guardPending(LeaveRequest $leaveRequest): void
    {
        if (! $leaveRequest->isPending()) {
            throw new RuntimeException('This request has already been reviewed.');
        }
    }

    private function syncAttendance(LeaveRequest $leaveRequest): void
    {
        foreach (CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date) as $date) {
            if ($date->isWeekend()) {
                continue;
            }

            Attendance::updateOrCreate(
                ['employee_id' => $leaveRequest->employee_id, 'date' => $date->toDateString()],
                [
                    'status' => 'on_leave',
                    'check_in' => null,
                    'check_out' => null,
                    'worked_hours' => null,
                    'notes' => "{$leaveRequest->leaveType->name} leave",
                ]
            );
        }
    }

    private function notifyReviewers(LeaveRequest $leaveRequest): void
    {
        $reviewers = User::permission('leaves.approve')
            ->where('id', '!=', $leaveRequest->employee->user_id)
            ->get();

        if ($reviewers->isNotEmpty()) {
            Notification::send($reviewers, new LeaveRequestSubmitted($leaveRequest));
        }
    }

    private function notifyEmployee(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->employee->user?->notify(new LeaveRequestReviewed($leaveRequest));
    }
}
