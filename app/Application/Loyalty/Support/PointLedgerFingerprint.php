<?php

namespace App\Application\Loyalty\Support;

use App\Application\Loyalty\DTOs\AdjustPointsDTO;
use App\Application\Loyalty\DTOs\AwardPointsDTO;
use App\Application\Loyalty\DTOs\ReversePointEntryDTO;
use App\Application\Loyalty\DTOs\SpendPointsDTO;
use App\Domain\Loyalty\Enums\PointEntryDirectionEnum;
use App\Domain\Loyalty\Enums\PointEntryTypeEnum;
use JsonException;

class PointLedgerFingerprint
{
    /** @throws JsonException */
    public function forAward(AwardPointsDTO $data): string
    {
        return $this->make([
            'version' => 1,
            'point_account_id' => $data->account->getKey(),
            'type' => PointEntryTypeEnum::EARN->value,
            'direction' => PointEntryDirectionEnum::CREDIT->value,
            'amount' => $data->amount,
            'reason' => $data->reason,
            'earning_rule_id' => $data->earningRule?->getKey(),
            'actor_user_id' => $data->actor?->getKey(),
            'reference_type' => $data->reference?->getMorphClass(),
            'reference_id' => $data->reference?->getKey(),
        ]);
    }

    /** @throws JsonException */
    public function forSpend(SpendPointsDTO $data): string
    {
        return $this->make([
            'version' => 1,
            'point_account_id' => $data->account->getKey(),
            'type' => PointEntryTypeEnum::SPEND->value,
            'direction' => PointEntryDirectionEnum::DEBIT->value,
            'amount' => $data->amount,
            'reason' => $data->reason,
            'actor_user_id' => $data->actor?->getKey(),
            'reference_type' => $data->reference?->getMorphClass(),
            'reference_id' => $data->reference?->getKey(),
        ]);
    }

    /** @throws JsonException */
    public function forAdjustment(AdjustPointsDTO $data): string
    {
        return $this->make([
            'version' => 1,
            'point_account_id' => $data->account->getKey(),
            'type' => PointEntryTypeEnum::ADJUSTMENT->value,
            'direction' => $data->direction->value,
            'amount' => $data->amount,
            'reason' => $data->reason,
            'actor_user_id' => $data->actor?->getKey(),
            'reference_type' => $data->reference?->getMorphClass(),
            'reference_id' => $data->reference?->getKey(),
        ]);
    }

    /** @throws JsonException */
    public function forReversal(ReversePointEntryDTO $data): string
    {
        return $this->make([
            'version' => 1,
            'point_account_id' => $data->entry->point_account_id,
            'type' => PointEntryTypeEnum::REVERSAL->value,
            'direction' => $data->entry->direction->opposite()->value,
            'amount' => $data->amount,
            'reversal_of_id' => $data->entry->getKey(),
            'actor_user_id' => $data->actor?->getKey(),
            'reference_type' => $data->reference?->getMorphClass(),
            'reference_id' => $data->reference?->getKey(),
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
