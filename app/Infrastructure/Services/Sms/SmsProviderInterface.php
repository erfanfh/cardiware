<?php

namespace App\Infrastructure\Services\Sms;

/**
 * This interface defines the contract for an SMS provider class.
 * Any SMS provider class must implement the send method.
 */
interface SmsProviderInterface
{
    /**
     * Sends an SMS message.
     *
     * @param string $mobile
     * @param string $message
     * @return bool
     */
    public function send(string $mobile, string $message): bool;
}
