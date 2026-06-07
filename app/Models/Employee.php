<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'department_id',
        'employee_code',
        'first_name',
        'last_name',
        'phone',
        'gender',
        'date_of_birth',
        'job_title',
        'hire_date',
        'employment_status',
        'base_salary',
        'address',
        'avatar_path',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'base_salary' => 'decimal:2',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(EmployeeFile::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('first_name', 'like', "%{$s}%")
                ->orWhere('last_name', 'like', "%{$s}%")
                ->orWhere('employee_code', 'like', "%{$s}%")
                ->orWhere('job_title', 'like', "%{$s}%")
            )
            )
            ->when($filters['department_id'] ?? null, fn ($q, $d) => $q->where('department_id', $d)
            )
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('employment_status', $s)
            );
    }

    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->hasRole(['Admin', 'HR Manager'])) {
            return $query;
        }

        if ($user->hasRole('Team Lead')) {
            $leadEmployee = $user->employee;

            return $leadEmployee
                ? $query->where('department_id', $leadEmployee->department_id)
                : $query->whereRaw('0 = 1');
        }

        $self = $user->employee;

        return $self
            ? $query->where('id', $self->id)
            : $query->whereRaw('0 = 1');
    }

    public static function generateCode(): string
    {
        $year = now()->year;
        $count = static::withTrashed()
            ->where('employee_code', 'like', "EMP-{$year}-%")
            ->count();

        return sprintf('EMP-%d-%04d', $year, $count + 1);
    }
}
