<?php

namespace App\Domain\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellerPeriodClosure extends Model
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

    public function rewards(): HasMany
    {
        return $this->hasMany(StoreReward::class, 'closure_id')->orderBy('rank');
    }
}
