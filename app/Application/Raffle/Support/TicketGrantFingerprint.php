<?php

namespace App\Application\Raffle\Support;

use App\Application\Raffle\DTOs\IssueDrawTicketsDTO;
use JsonException;

class TicketGrantFingerprint
{
    /** @throws JsonException */
    public function forIssue(IssueDrawTicketsDTO $data): string
    {
        $intent = [
            'version' => 1,
            'campaign_id' => $data->campaign->getKey(),
            'user_id' => $data->user->getKey(),
            'quantity' => $data->quantity,
            'source_type' => $data->source?->getMorphClass(),
            'source_id' => $data->source?->getKey(),
            'issued_by_user_id' => $data->issuedBy?->getKey(),
        ];

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
