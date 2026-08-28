<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\PointEntryDirectionEnum;
use App\Domain\Loyalty\Enums\PointEntryTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use LogicException;

class PointLedgerEntry extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'point_account_id',
        'earning_rule_id',
        'actor_user_id',
        'type',
        'direction',
        'reason',
        'amount',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'reversal_of_id',
        'idempotency_key',
        'idempotency_fingerprint',
        'description',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'type' => PointEntryTypeEnum::class,
        'direction' => PointEntryDirectionEnum::class,
        'amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'metadata' => 'array',
        'occurred_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PointLedgerEntry $entry): void {
            if (blank($entry->public_token)) {
                $entry->public_token = (string)Str::ulid();
            }

            if ((int)$entry->amount <= 0) {
                throw new LogicException('Point ledger entry amount must be greater than zero.');
            }

            if (!$entry->type->supportsDirection($entry->direction)) {
                throw new LogicException('Point ledger entry direction does not match its type.');
            }
        });

        static::updating(function (): never {
            throw new LogicException('Point ledger entries cannot be updated.');
        });

        static::deleting(function (): never {
            throw new LogicException('Point ledger entries cannot be deleted.');
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(PointAccount::class, 'point_account_id');
    }

    public function earningRule(): BelongsTo
    {
        return $this->belongsTo(EarningRule::class, 'earning_rule_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }

    public function reversals(): HasMany
    {
        return $this->hasMany(self::class, 'reversal_of_id');
    }

    public function signedAmount(): int
    {
        return $this->direction->isCredit()
            ? (int)$this->amount
            : -(int)$this->amount;
    }
}
