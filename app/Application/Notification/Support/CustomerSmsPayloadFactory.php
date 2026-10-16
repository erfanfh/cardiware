<?php

namespace App\Application\Notification\Support;

use App\Domain\Notification\Enums\CustomerSmsType;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use App\Infrastructure\Services\Sms\Providers\FarazSmsProvider;
use App\Infrastructure\Services\Sms\Providers\MeliPayamakProvider;
use App\Infrastructure\Services\Sms\Providers\SmsIrProvider;
use RuntimeException;

final class CustomerSmsPayloadFactory
{
    public function __construct(private readonly GeneralSettings $settings) {}

    /**
     * @param  array<string, string>  $values
     * @return array{pattern_id: string, values: array<string|int, string>}
     */
    public function make(CustomerSmsType $type, array $values): array
    {
        $provider = match ($this->settings->get('default_sms_provider')) {
            SmsIrProvider::class => 'smsir',
            FarazSmsProvider::class => 'farazsms',
            MeliPayamakProvider::class => 'melipayamak',
            default => throw new RuntimeException('No supported SMS provider is selected for customer notifications.'),
        };
        $prefix = $provider.'_'.$type->value;
        $pattern = trim((string) $this->settings->get($prefix.'_pattern'));
        if ($pattern === '') {
            throw new RuntimeException("Customer SMS pattern [{$prefix}] is not configured.");
        }

        $mapped = [];
        foreach ($type->parameters() as $key => $defaultName) {
            if (! array_key_exists($key, $values)) {
                throw new RuntimeException("Customer SMS value [{$key}] is missing.");
            }
            $name = $provider === 'melipayamak'
                ? $key
                : trim((string) $this->settings->get($prefix.'_'.$key.'_parameter', $defaultName));
            if ($name === '' || array_key_exists($name, $mapped)) {
                throw new RuntimeException('Customer SMS parameter names must be non-empty and unique.');
            }
            $mapped[$name] = $values[$key];
        }

        return ['pattern_id' => $pattern, 'values' => $provider === 'melipayamak' ? array_values($mapped) : $mapped];
    }
}
