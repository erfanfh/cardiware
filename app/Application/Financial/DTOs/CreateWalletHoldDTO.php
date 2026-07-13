<?php

namespace App\Application\Financial\DTOs;

use App\Domain\Financial\Enums\WalletHoldTypeEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Identity\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

final readonly class CreateWalletHoldDTO
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public Wallet             $wallet,
        public WalletHoldTypeEnum $type,
        public int                $amount,
        public string             $idempotencyKey,
        public ?User              $actor = null,
        public ?Model             $reference = null,
        public ?string            $description = null,
        public array              $metadata = [],
        public ?CarbonInterface   $expiresAt = null,
    )
    {
    }
}
