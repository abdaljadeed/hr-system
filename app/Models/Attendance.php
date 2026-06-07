<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'worked_hours',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'check_in' => 'datetime',
            'check_out' => 'datetime',
            'worked_hours' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getWorkedHoursFormattedAttribute(): string
    {
        if (is_null($this->worked_hours)) {
            return '—';
        }

        $total = (int) round($this->worked_hours * 60);
        $hours = intdiv($total, 60);
        $minutes = $total % 60;

        return "{$hours}h {$minutes}m";
    }

    public function scopeForEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('date', $date);
    }

    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->hasRole(['Admin', 'HR Manager'])) {
            return $query;
        }

        if ($user->hasRole('Team Lead')) {
            $leadEmployee = $user->employee;
            $deptId = $leadEmployee?->department_id;

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
