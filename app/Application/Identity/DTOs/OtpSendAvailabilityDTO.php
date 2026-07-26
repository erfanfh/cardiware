<?php

namespace App\Application\Identity\DTOs;

final readonly class OtpSendAvailabilityDTO
{
    public function __construct(
        public bool $canSend,
        public int  $retryAfterSeconds = 0,
        public int  $expiresAfterSeconds = 0,
    )
    {
    }
}
