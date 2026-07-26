<?php

namespace App\Application\Identity\DTOs;

final readonly class ResetUserPasswordWithOtpDTO
{
    public function __construct(
        public string $mobile,
        public string $otpCode,
        public string $password,
    )
    {
    }
}
