<?php

namespace App\Application\Financial\Contracts;

interface Payable
{
    /**
     * Get payable amount in toman.
     */
    public function getPayableAmount(): int;

    /** Tax already included in the payable amount. */
    public function getPayableTaxAmount(): int;

    /**
     * Get payable display title.
     */
    public function getPayableTitle(): string;

    /**
     * Determine whether this payable can start payment.
     */
    public function canStartPayment(): bool;
}
