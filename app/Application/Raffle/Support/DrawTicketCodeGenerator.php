<?php

namespace App\Application\Raffle\Support;

use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawTicket;
use RuntimeException;

class DrawTicketCodeGenerator
{
    private const ALPHABET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    /**
     * @return list<string>
     */
    public function generateBatch(DrawCampaign $campaign, int $quantity): array
    {
        if ($quantity <= 0) {
            return [];
        }

        $campaignPart = strtoupper(base_convert((string)$campaign->getKey(), 10, 36));
        $accepted = [];
        $round = 0;

        while (count($accepted) < $quantity) {
            $round++;

            if ($round > 20) {
                throw new RuntimeException('Unable to generate unique raffle ticket codes.');
            }

            $needed = $quantity - count($accepted);
            $candidates = [];

            while (count($candidates) < $needed) {
                $code = 'MW-' . $campaignPart . '-' . $this->randomPart(10);

                if (!isset($accepted[$code])) {
                    $candidates[$code] = true;
                }
            }

            $candidateCodes = array_keys($candidates);
            $existing = [];

            foreach (array_chunk($candidateCodes, 500) as $chunk) {
                foreach (
                    DrawTicket::query()
                        ->whereIn('code', $chunk)
                        ->pluck('code')
                        ->all() as $existingCode
                ) {
                    $existing[$existingCode] = true;
                }
            }

            foreach ($candidateCodes as $code) {
                if (!isset($existing[$code])) {
                    $accepted[$code] = true;
                }
            }
        }

        return array_slice(array_keys($accepted), 0, $quantity);
    }

    private function randomPart(int $length): string
    {
        $result = '';
        $maxIndex = strlen(self::ALPHABET) - 1;

        for ($i = 0; $i < $length; $i++) {
            $result .= self::ALPHABET[random_int(0, $maxIndex)];
        }

        return $result;
    }
}
