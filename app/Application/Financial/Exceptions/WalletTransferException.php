<?php

namespace App\Application\Financial\Exceptions;

use RuntimeException;

class WalletTransferException extends RuntimeException
{
    public function __construct(
        public readonly string $reason,
        string                 $message,
    )
    {
        parent::__construct($message);
    }

    public static function invalidRecipientMobile(): self
    {
        return new self(
            reason: 'invalid_recipient_mobile',
            message: 'The recipient mobile number is invalid.',
        );
    }

    public static function recipientNotFound(): self
    {
        return new self(
            reason: 'recipient_not_found',
            message: 'The wallet transfer recipient was not found.',
        );
    }

    public static function cannotTransferToSelf(): self
    {
        return new self(
            reason: 'cannot_transfer_to_self',
            message: 'A user cannot transfer wallet balance to themselves.',
        );
    }

    public static function senderWalletUnavailable(): self
    {
        return new self(
            reason: 'sender_wallet_unavailable',
            message: 'The sender wallet is unavailable.',
        );
    }

    public static function recipientWalletUnavailable(): self
    {
        return new self(
            reason: 'recipient_wallet_unavailable',
            message: 'The recipient wallet is unavailable.',
        );
    }

    public static function insufficientWithdrawableBalance(): self
    {
        return new self(
            reason: 'insufficient_withdrawable_balance',
            message: 'The sender does not have enough withdrawable balance.',
        );
    }

    public static function requestConflict(): self
    {
        return new self(
            reason: 'request_conflict',
            message: 'The transfer request key has already been used for another operation.',
        );
    }

    public static function transferAlreadyFailed(): self
    {
        return new self(
            reason: 'transfer_already_failed',
            message: 'The wallet transfer has already failed and cannot be retried.',
        );
    }
}
