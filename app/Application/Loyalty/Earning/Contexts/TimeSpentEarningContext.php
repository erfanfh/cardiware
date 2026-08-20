<?php

namespace App\Application\Loyalty\Earning\Contexts;

use App\Application\Loyalty\Earning\Contracts\EarningContext;
use App\Domain\Identity\Models\User;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

final readonly class TimeSpentEarningContext implements EarningContext
{
    public CarbonImmutable $occurredAt;

    public function __construct(
        public User $user,
        public string $targetType,
        public int $targetId,
        public int $durationSeconds,
        public ?Model $reference = null,
        ?DateTimeInterface $occurredAt = null,
    ) {
        $this->occurredAt = $occurredAt
            ? CarbonImmutable::instance($occurredAt)
            : now()->toImmutable();
    }

    public function user(): User
    {
        return $this->user;
    }

    public function occurredAt(): CarbonImmutable
    {
        return $this->occurredAt;
    }

    public function reference(): ?Model
    {
        return $this->reference;
    }
}
