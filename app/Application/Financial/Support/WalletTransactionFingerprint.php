<?php

namespace App\Application\Financial\Support;

use App\Application\Financial\DTOs\CreditWalletData;
use App\Application\Financial\DTOs\DebitWalletDTO;
use App\Domain\Financial\Enums\WalletTransactionDirectionEnum;
use JsonException;

class WalletTransactionFingerprint
{
    /**
     * @throws JsonException
     */
    public function forCredit(CreditWalletData $data): string
    {
        return $this->make([
            'version' => 1,

            'wallet_id' => $data->wallet->getKey(),

            'direction' =>
                WalletTransactionDirectionEnum::CREDIT->value,

            'type' => $data->type->value,

            'amount' => $data->amount,

            'withdrawable_amount' =>
                $data->withdrawableAmount,

            'actor_user_id' =>
                $data->actor?->getKey(),

            'reference_type' =>
                $data->reference?->getMorphClass(),

            'reference_id' =>
                $data->reference?->getKey(),
        ]);
    }

    /**
     * @throws JsonException
     */
    public function forDebit(DebitWalletDTO $data): string
    {
        return $this->make([
            'version' => 1,

            'wallet_id' => $data->wallet->getKey(),

            'direction' =>
                WalletTransactionDirectionEnum::DEBIT->value,

            'type' => $data->type->value,

            'amount' => $data->amount,

            'debit_policy' => $data->policy->value,

            'actor_user_id' =>
                $data->actor?->getKey(),

            'reference_type' =>
                $data->reference?->getMorphClass(),

            'reference_id' =>
                $data->reference?->getKey(),
        ]);
    }

    /**
     * @param array<string, mixed> $intent
     * @throws JsonException
     */
    private function make(array $intent): string
    {
        ksort($intent);

        return hash(
            'sha256',
            json_encode(
                $intent,
                JSON_THROW_ON_ERROR
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES,
            ),
        );
    }
}
