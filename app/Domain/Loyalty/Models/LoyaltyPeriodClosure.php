<?php

namespace App\Domain\Loyalty\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyPeriodClosure extends Model
{
    protected $fillable = [
        'period_type', 'period_key', 'period_label', 'starts_at', 'ends_at', 'closed_at', 'winners_count',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'closed_at' => 'datetime',
        'winners_count' => 'integer',
    ];

    public function champions(): HasMany
    {
        return $this->hasMany(LoyaltyChampion::class, 'closure_id')->orderBy('rank');
    }
}
