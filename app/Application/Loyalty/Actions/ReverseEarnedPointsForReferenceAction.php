<?php

namespace App\Application\Loyalty\Actions;

use App\Application\Loyalty\DTOs\ReversePointEntryDTO;
use App\Application\Loyalty\Exceptions\PointReversalExceededException;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\PointEntryTypeEnum;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class ReverseEarnedPointsForReferenceAction
{
    public function __construct(
        private readonly ReversePointEntryAction $reversePointEntryAction,
    ) {
    }

    /**
     * Reverse every still-active EARN entry for the given logical source.
     *
     * @param array<string, mixed> $metadata
     * @return Collection<int, PointLedgerEntry>
     * @throws Throwable
     */
    public function execute(
        User $user,
        Model $reference,
        string $reason,
        string $eventKey,
        ?CarbonInterface $occurredAt = null,
        array $metadata = [],
        ?string $description = null,
    ): Collection {
        $this->validate($user, $reference, $reason, $eventKey);

        $account = PointAccount::query()
            ->where('user_id', $user->getKey())
            ->first();

        if (!$account) {
            return new Collection();
        }

        $entries = PointLedgerEntry::query()
            ->where('point_account_id', $account->getKey())
            ->where('type', PointEntryTypeEnum::EARN)
            ->where('reason', $reason)
            ->where('reference_type', $reference->getMorphClass())
            ->where('reference_id', $reference->getKey())
            ->orderBy('id')
            ->get();

        $reversals = new Collection();

        foreach ($entries as $entry) {
            $alreadyReversed = (int)PointLedgerEntry::query()
                ->where('reversal_of_id', $entry->getKey())
                ->sum('amount');

            $remaining = max(0, (int)$entry->amount - $alreadyReversed);

            if ($remaining === 0) {
                continue;
            }

            try {
                $reversals->push(
                    $this->reversePointEntryAction->execute(
                        new ReversePointEntryDTO(
                            entry: $entry,
                            amount: $remaining,
                            idempotencyKey: "{$eventKey}:entry:{$entry->getKey()}",
                            reference: $reference,
                            description: $description,
                            metadata: $metadata,
                            occurredAt: $occurredAt,
                        )
                    )
                );
            } catch (PointReversalExceededException) {
                // Another concurrent reversal already consumed the reversible amount.
                // The ledger remains correct, so this event can safely continue.
                continue;
            }
        }

        return $reversals;
    }

    private function validate(
        User $user,
        Model $reference,
        string $reason,
        string $eventKey,
    ): void {
        if (!$user->exists) {
            throw new InvalidArgumentException('User must exist before earned points can be reversed.');
        }

        if (!$reference->exists) {
            throw new InvalidArgumentException('Reference must exist before earned points can be reversed.');
        }

        if (trim($reason) === '' || Str::length($reason) > 80) {
            throw new InvalidArgumentException('Point earning reason is required and cannot exceed 80 characters.');
        }

        if (trim($eventKey) === '' || Str::length($eventKey) > 130) {
            throw new InvalidArgumentException('Point reversal event key is required and cannot exceed 130 characters.');
        }
    }
}
