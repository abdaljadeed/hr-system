<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'default_days',
        'is_paid',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_days' => 'decimal:1',
            'is_paid' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function requests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
