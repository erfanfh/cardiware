<?php

namespace App\Application\Poll\Support;

use App\Domain\Identity\Models\User;

final readonly class PollVoterIdentity
{
    public function __construct(
        public string $key,
        public string $ipHash,
        public ?int $userId,
    ) {}

    public static function resolve(?User $user, ?string $ip): self
    {
        $normalizedIp = trim((string) $ip) ?: 'unknown';
        $secret = (string) config('app.key');
        $ipHash = hash_hmac('sha256', $normalizedIp, $secret);

        return new self(
            key: $user ? 'user:'.$user->getKey() : 'guest:'.$ipHash,
            ipHash: $ipHash,
            userId: $user?->getKey(),
        );
    }
}
