<?php

namespace App\Application\Identity\DTOs;

final readonly class OtpValidationResultDTO
{
    public function __construct(
        public bool   $valid,
        public string $message = '',
        public bool   $incrementAttempts = false,
        public bool   $invalidateOtp = false,
    )
    {
    }

    public static function valid(): self
    {
        return new self(
            valid: true,
        );
    }

    public static function invalid(
        string $message,
        bool   $incrementAttempts = false,
        bool   $invalidateOtp = false,
    ): self
    {
        return new self(
            valid: false,
            message: $message,
            incrementAttempts: $incrementAttempts,
            invalidateOtp: $invalidateOtp,
        );
    }
}
