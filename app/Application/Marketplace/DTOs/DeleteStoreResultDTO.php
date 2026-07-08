<?php

namespace App\Application\Marketplace\DTOs;

class DeleteStoreResultDTO
{
    public function __construct(
        public bool   $status,
        public string $message = '',
    )
    {
    }

}
