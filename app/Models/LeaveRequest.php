<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'days' => 'decimal:1',
            'reviewed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeForEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->hasRole(['Admin', 'HR Manager'])) {
            return $query;
        }

        if ($user->hasRole('Team Lead')) {
            $deptId = $user->employee?->department_id;

            return $deptId
                ? $query->whereHas('employee', fn ($q) => $q->where('department_id', $deptId))
                : $query->whereRaw('0 = 1');
        }

        $self = $user->employee;

        return $self
            ? $query->where('employee_id', $self->id)
            : $query->whereRaw('0 = 1');
    }
}
