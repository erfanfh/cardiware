<?php

namespace App\Infrastructure\Services\Sms;

use RuntimeException;

class SmsService
{
    private SmsProviderInterface $provider;

    public function __construct()
    {
        $providerClass = get_setting('general', 'default_sms_provider');

        if (blank($providerClass) || !class_exists($providerClass)) {
            throw new RuntimeException('No valid SMS provider configuration found.');
        }

        $provider = app($providerClass);

        if (!$provider instanceof SmsProviderInterface) {
            throw new RuntimeException("SMS provider [$providerClass] must implement SmsProviderInterface.");
        }

        $this->provider = $provider;
    }

    public function sendOtp(string $mobile, string $code): string
    {
        return $this->provider->sendOtp($mobile, $code);
    }

    public function sendPattern(string $mobile, string $patternId, array $values): string
    {
        return $this->provider->sendPattern($mobile, $patternId, $values);
    }
}
