<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointAccount extends Model
{
    protected $fillable = [
        'user_id',
    ];

    protected $casts = [
        'balance' => 'integer',
        'lifetime_earned' => 'integer',
        'lifetime_spent' => 'integer',
        'lifetime_xp' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(PointLedgerEntry::class);
    }

    protected function spendableBalance(): Attribute
    {
        return Attribute::make(
            get: fn(): int => max(0, (int)$this->balance),
        );
    }
}
