<?php

namespace App\Domain\Financial\Events;

final readonly class WalletWithdrawalResolvedEvent
{
    public function __construct(public int $withdrawalId) {}
}
