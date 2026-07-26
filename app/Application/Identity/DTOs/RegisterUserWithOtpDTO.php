<?php

namespace App\Application\Identity\DTOs;

final readonly class RegisterUserWithOtpDTO
{
    public function __construct(
        public string  $mobile,
        public string  $otpCode,
        public string  $firstName,
        public string  $lastName,
        public ?string $email,
        public string  $password,
    )
    {
    }
}
