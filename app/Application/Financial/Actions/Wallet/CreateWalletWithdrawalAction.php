<?php

namespace App\Application\Financial\Actions\Wallet;

use App\Application\Financial\DTOs\CreateWalletHoldDTO;
use App\Domain\Financial\Enums\WalletHoldTypeEnum;
use App\Domain\Financial\Enums\WalletWithdrawalStatusEnum;
use App\Domain\Financial\Models\WalletWithdrawal;
use App\Domain\Identity\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use DomainException;

class CreateWalletWithdrawalAction
{
    public function __construct(
        private readonly GetOrCreateWalletAction $getOrCreateWallet,
        private readonly CreateWalletHoldAction $createHold,
    ) {}

    public function execute(User $user, int $amount, string $iban, string $cardNumber, string $accountHolder, ?string $note, string $requestKey): WalletWithdrawal
    {
        $key = 'wallet-withdrawal:' . $requestKey;
        $existing = WalletWithdrawal::query()->where('idempotency_key', $key)->first();
        if ($existing) {
            return $this->assertSameIntent($existing, $user, $amount, $iban, $cardNumber, $accountHolder);
        }

        if (!get_setting('financial', 'withdrawal_enabled', false)) {
            throw new DomainException('درخواست برداشت وجه در حال حاضر غیرفعال است.');
        }

        $wallet = $this->getOrCreateWallet->execute($user);

        try {
            return DB::transaction(function () use ($user, $wallet, $amount, $iban, $cardNumber, $accountHolder, $note, $key, $requestKey): WalletWithdrawal {
                $withdrawal = WalletWithdrawal::query()->create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'idempotency_key' => $key,
                    'status' => WalletWithdrawalStatusEnum::PENDING,
                    'amount' => $amount,
                    'iban' => $iban,
                    'card_number' => $cardNumber,
                    'account_holder' => $accountHolder,
                    'user_note' => $note,
                ]);

                $hold = $this->createHold->execute(new CreateWalletHoldDTO(
                    wallet: $wallet,
                    type: WalletHoldTypeEnum::WITHDRAWAL_REQUEST,
                    amount: $amount,
                    idempotencyKey: 'wallet-withdrawal-hold:' . $requestKey,
                    actor: $user,
                    reference: $withdrawal,
                    description: 'رزرو درخواست برداشت وجه',
                    metadata: ['source' => 'wallet_withdrawal', 'withdrawal_id' => $withdrawal->id],
                ));

                $withdrawal->forceFill(['hold_id' => $hold->id])->save();

                return $withdrawal->refresh();
            }, 3);
        } catch (QueryException $exception) {
            $existing = WalletWithdrawal::query()->where('idempotency_key', $key)->first();
            if ($existing) {
                return $this->assertSameIntent($existing, $user, $amount, $iban, $cardNumber, $accountHolder);
            }
            throw $exception;
        }
    }

    private function assertSameIntent(WalletWithdrawal $withdrawal, User $user, int $amount, string $iban, string $cardNumber, string $accountHolder): WalletWithdrawal
    {
        if ($withdrawal->user_id !== $user->id
            || $withdrawal->amount !== $amount
            || $withdrawal->iban !== $iban
            || $withdrawal->card_number !== $cardNumber
            || $withdrawal->account_holder !== $accountHolder) {
            throw new DomainException('شناسه درخواست قبلاً برای برداشت دیگری استفاده شده است.');
        }

        return $withdrawal;
    }
}
