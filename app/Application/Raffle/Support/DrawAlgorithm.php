<?php

namespace App\Application\Raffle\Support;

class DrawAlgorithm
{
    public const NAME = 'hmac-sha256-ticket-v1';

    public function score(string $seed, string $campaignPublicToken, string $ticketPublicToken): string
    {
        return hash_hmac(
            'sha256',
            'raffle-draw-v1|' . $campaignPublicToken . '|' . $ticketPublicToken,
            $seed,
        );
    }
}
