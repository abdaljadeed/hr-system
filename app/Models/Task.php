<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    public const STATUSES = ['todo', 'in_progress', 'submitted', 'approved', 'rejected'];

    public const TRANSITIONS = [
        'todo' => ['in_progress'],
        'in_progress' => ['submitted'],
        'submitted' => ['approved', 'rejected'],
        'rejected' => ['in_progress'],
        'approved' => [],
    ];

    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'assigned_by',
        'priority',
        'status',
        'due_date',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date:Y-m-d',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TaskHistory::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && ! in_array($this->status, ['approved', 'rejected'], true)
            && $this->due_date->isPast();
    }

    public function getIsDueSoonAttribute(): bool
    {
        if (! $this->due_date || in_array($this->status, ['approved', 'rejected'], true)) {
            return false;
        }

        $deadline = $this->due_date->copy()->endOfDay();

        return $deadline->isFuture() && now()->diffInHours($deadline) <= 24;
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['priority'] ?? null, fn ($q, $p) => $q->where('priority', $p))
            ->when($filters['assigned_to'] ?? null, fn ($q, $a) => $q->where('assigned_to', $a))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"));
    }

    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->hasRole(['Admin', 'HR Manager'])) {
            return $query;
        }

        if ($user->hasRole('Team Lead')) {
            $deptId = $user->employee?->department_id;

            if (! $deptId) {
                return $query->where('assigned_by', $user->id);
            }

            $deptUserIds = Employee::where('department_id', $deptId)
                ->whereNotNull('user_id')
                ->pluck('user_id');

            return $query->where(fn ($q) => $q
                ->where('assigned_by', $user->id)
                ->orWhereIn('assigned_to', $deptUserIds));
        }

        return $query->where(fn ($q) => $q
            ->where('assigned_to', $user->id)
            ->orWhere('assigned_by', $user->id));
    }
}
