<?php

namespace App\Application\Loyalty\Earning\Contexts;

use App\Application\Loyalty\Earning\Contracts\EarningContext;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Identity\Models\User;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

final readonly class CommentEarningContext implements EarningContext
{
    public CarbonImmutable $occurredAt;

    public function __construct(
        public User $user,
        public Comment $comment,
        ?DateTimeInterface $occurredAt = null,
    ) {
        $this->occurredAt = $occurredAt
            ? CarbonImmutable::instance($occurredAt)
            : ($comment->approved_at?->toImmutable()
                ?? $comment->created_at?->toImmutable()
                ?? now()->toImmutable());
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
        return $this->comment;
    }
}
