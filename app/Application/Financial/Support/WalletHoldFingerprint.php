<?php

namespace App\Application\Financial\Support;

use App\Application\Financial\DTOs\CreateWalletHoldDTO;
use JsonException;

class WalletHoldFingerprint
{
    /**
     * @throws JsonException
     */
    public function forCreate(
        CreateWalletHoldDTO $data,
    ): string
    {
        return $this->make([
            'version' => 1,

            'wallet_id' =>
                $data->wallet->getKey(),

            'type' =>
                $data->type->value,

            'debit_policy' =>
                $data->type->debitPolicy()->value,

            'amount' =>
                $data->amount,

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
