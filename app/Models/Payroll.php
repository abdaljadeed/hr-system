<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'period_year',
        'period_month',
        'base_salary',
        'worked_days',
        'absent_days',
        'unpaid_leave_days',
        'total_bonuses',
        'total_deductions',
        'net_salary',
        'status',
        'generated_by',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'period_year' => 'integer',
            'period_month' => 'integer',
            'base_salary' => 'decimal:2',
            'total_bonuses' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'finalized_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function getNetSalaryFormattedAttribute(): string
    {
        return number_format((float) $this->net_salary, 2);
    }

    public function getPeriodLabelAttribute(): string
    {
        return Carbon::create($this->period_year, $this->period_month, 1)->format('F Y');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->where('period_year', $year)->where('period_month', $month);
    }

    public function scopeForEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->can('payroll.generate')) {
            return $query;
        }

        $self = $user->employee;

        return $self
            ? $query->where('employee_id', $self->id)->whereIn('status', ['finalized', 'paid'])
            : $query->whereRaw('0 = 1');
    }
}
