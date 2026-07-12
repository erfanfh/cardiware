<?php

namespace App\Application\Financial\Exceptions;

use DomainException;

class WalletHoldIdempotencyConflictException extends DomainException
{
}
