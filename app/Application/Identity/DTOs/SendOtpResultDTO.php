<?php

namespace App\Application\Identity\DTOs;

class SendOtpResultDTO
{
    public function __construct(
        public bool   $sent,
        public bool   $alreadySent = false,
        public string $message = '',
    )
    {
    }
}
