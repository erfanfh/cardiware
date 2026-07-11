<?php

namespace App\Domain\Financial\Enums;

enum WalletDebitPolicyEnum: string
{
    /**
     * Spend non-withdrawable credit first.
     *
     * Appropriate for:
     * - order payments
     * - service purchases
     */
    case NON_WITHDRAWABLE_FIRST = 'non_withdrawable_first';

    /**
     * Spend withdrawable balance first.
     *
     * This should rarely be used.
     */
    case WITHDRAWABLE_FIRST = 'withdrawable_first';

    /**
     * The entire amount must come from withdrawable balance.
     *
     * Appropriate for:
     * - user transfers
     * - withdrawal requests
     */
    case WITHDRAWABLE_ONLY = 'withdrawable_only';

    public function label(): string
    {
        return match ($this) {
            self::NON_WITHDRAWABLE_FIRST => 'ابتدا موجودی غیرقابل برداشت',
            self::WITHDRAWABLE_FIRST => 'ابتدا موجودی قابل برداشت',
            self::WITHDRAWABLE_ONLY => 'فقط موجودی قابل برداشت',
        };
    }
}
