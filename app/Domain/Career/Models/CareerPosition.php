<?php

namespace App\Domain\Career\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerPosition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'department', 'employment_type', 'cooperation_type',
        'location', 'level', 'detail_label', 'detail_value', 'summary',
        'requirements', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'requirements' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(CareerApplication::class);
    }

    public function scopePubliclyAvailable(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}
