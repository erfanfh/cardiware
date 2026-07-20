<?php

namespace App\Application\Financial\DTOs;

use App\Domain\Identity\Models\User;

final readonly class TransferWalletBalanceDTO
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public User   $sender,
        public string $recipientMobile,
        public int    $amount,
        public string $requestKey,
        public array  $metadata = [],
    )
    {
    }
}
