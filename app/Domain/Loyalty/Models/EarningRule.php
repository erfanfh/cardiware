<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EarningRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'title',
        'type',
        'is_active',
        'priority',
        'starts_at',
        'ends_at',
        'config',
    ];

    protected $casts = [
        'type' => EarningRuleTypeEnum::class,
        'is_active' => 'boolean',
        'priority' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'config' => 'array',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(PointLedgerEntry::class, 'earning_rule_id');
    }

    public function scopeCurrentlyActive(Builder $query): Builder
    {
        return $this->scopeActiveAt($query, now());
    }

    public function scopeActiveAt(Builder $query, CarbonInterface $at): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) use ($at): void {
                $query
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $at);
            })
            ->where(function (Builder $query) use ($at): void {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $at);
            });
    }

    public function isCurrentlyActive(): bool
    {
        return $this->isActiveAt(now());
    }

    public function isActiveAt(CarbonInterface $at): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->greaterThan($at)) {
            return false;
        }

        return !$this->ends_at || !$this->ends_at->lessThan($at);
    }
}
