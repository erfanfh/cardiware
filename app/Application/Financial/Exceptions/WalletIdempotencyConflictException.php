<?php

namespace App\Application\Financial\Exceptions;

use DomainException;

class WalletIdempotencyConflictException extends DomainException
{
}
