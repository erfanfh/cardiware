<?php

namespace App\Application\Identity\DTOs;

final readonly class SendOtpResultDTO
{
    public function __construct(
        public bool   $sent,
        public bool   $alreadySent,
        public string $message,
        public int    $retryAfterSeconds = 0,
        public int    $expiresAfterSeconds = 0,
    )
    {
    }
}
