<?php

namespace App\Application\Financial\Exceptions;

use DomainException;

class WalletTopUpIdempotencyConflictException extends DomainException
{
}
