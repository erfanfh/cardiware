<?php

namespace App\Application\Raffle\Support;

use App\Domain\Raffle\Models\DrawWinner;
use App\Infrastructure\Services\Setting\Groups\RaffleSettings;
use App\Infrastructure\Services\Sms\Providers\FarazSmsProvider;
use App\Infrastructure\Services\Sms\Providers\MeliPayamakProvider;
use App\Infrastructure\Services\Sms\Providers\SmsIrProvider;
use RuntimeException;

class RaffleWinnerSmsPayloadFactory
{
    public function __construct(private readonly RaffleSettings $settings) {}

    /** @return array{pattern_id:string, values:array<string|int, string>} */
    public function make(DrawWinner $winner, string $type): array
    {
        if (! in_array($type, ['winner', 'reminder'], true)) {
            throw new RuntimeException('Unsupported raffle SMS notification type.');
        }

        $winner->loadMissing(['user', 'campaign', 'prize']);
        $user = $winner->user;
        $name = trim((string) ($user?->display_name ?: $user?->full_name ?: $user?->mobile));
        $values = [
            'name' => $name ?: 'کاربر عزیز',
            'campaign' => (string) $winner->campaign?->title,
            'prize' => (string) $winner->prize?->title,
            'deadline' => $winner->confirmation_deadline_at
                ? jalali_date($winner->confirmation_deadline_at, 'Y/m/d H:i')
                : 'بدون محدودیت',
        ];

        $provider = get_setting('general', 'default_sms_provider');

        return match ($provider) {
            SmsIrProvider::class => $this->namedPayload('smsir', $type, 'template_id', 'parameter', $values),
            FarazSmsProvider::class => $this->namedPayload('farazsms', $type, 'pattern', 'attribute', $values),
            MeliPayamakProvider::class => $this->meliPayamakPayload($type, $values),
            default => throw new RuntimeException('No supported SMS provider is selected for raffle notifications.'),
        };
    }

    private function namedPayload(string $provider, string $type, string $idSuffix, string $nameSuffix, array $values): array
    {
        $patternId = trim((string) $this->settings->get("{$provider}_{$type}_{$idSuffix}"));
        if ($patternId === '') {
            throw new RuntimeException("Raffle {$type} SMS pattern is not configured for {$provider}.");
        }

        $mapped = [];
        foreach ($values as $key => $value) {
            $parameter = trim((string) $this->settings->get(
                "{$provider}_{$type}_{$key}_{$nameSuffix}",
                ucfirst($key),
            ));
            if ($parameter === '') {
                throw new RuntimeException("Raffle SMS parameter [{$key}] is not configured for {$provider}.");
            }
            $mapped[$parameter] = $value;
        }

        return ['pattern_id' => $patternId, 'values' => $mapped];
    }

    private function meliPayamakPayload(string $type, array $values): array
    {
        $patternId = trim((string) $this->settings->get("melipayamak_{$type}_pattern"));
        if ($patternId === '') {
            throw new RuntimeException("Raffle {$type} SMS pattern is not configured for MeliPayamak.");
        }

        return [
            'pattern_id' => $patternId,
            'values' => array_values($values),
        ];
    }
}
