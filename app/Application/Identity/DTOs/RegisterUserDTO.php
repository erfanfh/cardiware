<?php

namespace App\Application\Identity\DTOs;

use App\Domain\Identity\Enums\UserStatusEnum;

final readonly class RegisterUserDTO
{
    public function __construct(
        public string          $firstName,
        public string          $lastName,
        public string          $mobile,
        public string          $password,
        public ?string         $displayName = null,
        public ?string         $email = null,
        public ?string         $username = null,
        public ?int            $roleId = null,
        public ?UserStatusEnum $status = null,
    )
    {
    }
}
