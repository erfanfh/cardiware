<?php

namespace App\Application\Identity\Support;

use App\Application\Identity\DTOs\LoginAlertData;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use App\Infrastructure\Services\Sms\Providers\FarazSmsProvider;
use App\Infrastructure\Services\Sms\Providers\MeliPayamakProvider;
use App\Infrastructure\Services\Sms\Providers\SmsIrProvider;
use RuntimeException;

final class LoginAlertSmsPayloadFactory
{
    public function __construct(private readonly GeneralSettings $settings) {}

    /** @return array{pattern_id: string, values: array<string|int, string>} */
    public function make(LoginAlertData $login): array
    {
        $provider = match ($this->settings->get('default_sms_provider')) {
            SmsIrProvider::class => 'smsir',
            FarazSmsProvider::class => 'farazsms',
            MeliPayamakProvider::class => 'melipayamak',
            default => throw new RuntimeException('No supported SMS provider is selected for login alerts.'),
        };

        $pattern = trim((string) $this->settings->get("{$provider}_login_pattern"));
        if ($pattern === '') {
            throw new RuntimeException("Login alert SMS pattern is not configured for {$provider}.");
        }

        $values = [
            'name' => $login->name,
            'time' => $login->time(),
            'ip' => $login->ip,
            'source' => $login->sourceLabel(),
        ];

        if ($provider === 'melipayamak') {
            return ['pattern_id' => $pattern, 'values' => array_values($values)];
        }

        $mapped = [];
        foreach ($values as $key => $value) {
            $parameter = trim((string) $this->settings->get("{$provider}_login_{$key}_parameter", ucfirst($key)));
            if ($parameter === '' || isset($mapped[$parameter])) {
                throw new RuntimeException("Login alert SMS parameters must be non-empty and unique for {$provider}.");
            }
            $mapped[$parameter] = $value;
        }

        return ['pattern_id' => $pattern, 'values' => $mapped];
    }
}
