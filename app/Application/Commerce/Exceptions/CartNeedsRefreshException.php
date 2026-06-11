<?php

namespace App\Application\Commerce\Exceptions;

use RuntimeException;

class CartNeedsRefreshException extends RuntimeException
{
    public function __construct(
        private readonly array $syncResult,
        string                 $message = 'Cart needs refresh before checkout.'
    )
    {
        parent::__construct($message);
    }

    public function syncResult(): array
    {
        return $this->syncResult;
    }
}
