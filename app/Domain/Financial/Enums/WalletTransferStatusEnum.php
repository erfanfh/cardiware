<?php

namespace App\Domain\Financial\Enums;

enum WalletTransferStatusEnum: string
{
    /**
     * Transfer has been created but is not completed yet.
     */
    case PENDING = 'pending';

    /**
     * Both debit and credit operations have been completed.
     */
    case COMPLETED = 'completed';

    /**
     * Transfer could not be completed.
     */
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در حال انجام',
            self::COMPLETED => 'انجام شده',
            self::FAILED => 'ناموفق',
        };
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this === self::FAILED;
    }
}
