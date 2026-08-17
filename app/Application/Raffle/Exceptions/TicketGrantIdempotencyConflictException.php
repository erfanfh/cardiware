<?php

namespace App\Application\Raffle\Exceptions;

use DomainException;

class TicketGrantIdempotencyConflictException extends DomainException
{
}
