<?php

namespace App\Infrastructure\Services\Sms;

use RuntimeException;

/**
 * Handles the logic for sending SMS messages using a configured SMS provider.
 */
class SmsService
{
    private SmsProviderInterface $provider;

    /**
     * Initializes the SMS provider based on the application's configuration
     */
    public function __construct()
    {
        $providerConfig = get_setting('general', 'default_sms_provider');

        if (!isset($providerConfig))
            throw new RuntimeException('No valid SMS provider configuration found!');

        $this->provider = app($providerConfig);
    }

    /**
     * Sends an SMS message.
     *
     * @param string $mobile
     * @param string $message
     * @return bool
     */
    public function send(string $mobile, string $message): bool
    {
        return $this->provider->send($mobile, $message);
    }
}
