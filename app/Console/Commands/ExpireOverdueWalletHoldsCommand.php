<?php

namespace App\Console\Commands;

use App\Application\Financial\Actions\Wallet\ExpireOverdueWalletHoldsAction;
use Illuminate\Console\Command;
use Throwable;

class ExpireOverdueWalletHoldsCommand extends Command
{
    protected $signature = 'wallet-holds:expire';

    protected $description = 'Expire overdue active wallet holds and release held balances';

    public function handle(
        ExpireOverdueWalletHoldsAction $action,
    ): int
    {
        try {
            $expiredCount = $action->execute();

            $this->components->info(
                "{$expiredCount} wallet hold(s) expired."
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            $this->components->error(
                'Wallet hold expiration failed.'
            );

            return self::FAILURE;
        }
    }
}
