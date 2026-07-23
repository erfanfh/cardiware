<?php

namespace App\Domain\Financial\Enums;

enum FinancialActivitySourceEnum: string
{
    /**
     * A direct order payment attempt.
     */
    case PAYMENT = 'payment';

    /**
     * A normal or order-related wallet top-up.
     */
    case WALLET_TOP_UP = 'wallet_top_up';

    /**
     * A transfer between two user wallets.
     */
    case WALLET_TRANSFER = 'wallet_transfer';

    /**
     * A standalone wallet ledger transaction.
     */
    case WALLET_TRANSACTION = 'wallet_transaction';
}
