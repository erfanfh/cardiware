<?php

namespace App\Application\Financial\DTOs;

use App\Domain\Financial\Enums\WalletDebitPolicyEnum;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;

final readonly class DebitWalletData
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public Wallet                    $wallet,
        public WalletTransactionTypeEnum $type,
        public int                       $amount,
        public string                    $idempotencyKey,
        public WalletDebitPolicyEnum     $policy = WalletDebitPolicyEnum::NON_WITHDRAWABLE_FIRST,
        public ?User                     $actor = null,
        public ?Model                    $reference = null,
        public ?string                   $description = null,
        public array                     $metadata = [],
    )
    {
    }
}
