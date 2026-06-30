<?php

namespace App\Infrastructure\Services\Sms;

/**
 * This interface defines the contract for an SMS provider class.
 * Any SMS provider class must implement the send method.
 */
interface SmsProviderInterface
{
    public function sendPattern(string $mobile, string $patternId, array $values): string;
    
    public function sendOtp(string $mobile, string $code): string;
}
