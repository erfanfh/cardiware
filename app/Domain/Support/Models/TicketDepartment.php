<?php

namespace App\Domain\Support\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketDepartment extends Model
{
    protected $fillable = [
        'title',
        'requires_product',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'requires_product' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'department_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('title');
    }

    public function requiresProduct(): bool
    {
        return $this->requires_product;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}
