<?php

namespace App\Application\Financial\Actions\Wallet;

use App\Application\Financial\DTOs\CreditWalletDTO;
use App\Application\Financial\DTOs\DebitWalletDTO;
use App\Application\Financial\DTOs\TransferWalletBalanceDTO;
use App\Application\Financial\Exceptions\WalletTransferException;
use App\Domain\Financial\Enums\WalletDebitPolicyEnum;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Financial\Enums\WalletTransferStatusEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Financial\Models\WalletTransfer;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class TransferWalletBalanceAction
{
    public function __construct(
        private readonly DebitWalletAction  $debitWalletAction,
        private readonly CreditWalletAction $creditWalletAction,
    )
    {
    }

    /**
     * Atomically transfer withdrawable wallet balance between two users.
     *
     * @throws Throwable
     */
    public function execute(
        TransferWalletBalanceDTO $data,
    ): WalletTransfer
    {
        $this->validate($data);

        $requestKey = trim($data->requestKey);

        $recipientMobile = $this->normalizeIranianMobile(
            $data->recipientMobile,
        );

        $recipient = User::query()
            ->where('mobile', $recipientMobile)
            ->first();

        if (!$recipient) {
            throw WalletTransferException::recipientNotFound();
        }

        if ($recipient->is($data->sender)) {
            throw WalletTransferException::cannotTransferToSelf();
        }

        $wallets = Wallet::query()
            ->whereIn('user_id', [
                $data->sender->getKey(),
                $recipient->getKey(),
            ])
            ->get()
            ->keyBy('user_id');

        /** @var Wallet|null $senderWallet */
        $senderWallet = $wallets->get(
            $data->sender->getKey(),
        );

        /** @var Wallet|null $recipientWallet */
        $recipientWallet = $wallets->get(
            $recipient->getKey(),
        );

        if (!$senderWallet || !$senderWallet->allowsUserTransactions()) {
            throw WalletTransferException::senderWalletUnavailable();
        }

        /*
         * کیف پول مقصد نباید به صورت خودکار ساخته شود.
         * مقصد باید از قبل کیف پول فعال داشته باشد.
         */
        if (!$recipientWallet || !$recipientWallet->allowsUserTransactions()) {
            throw WalletTransferException::recipientWalletUnavailable();
        }

        /*
         * firstOrCreate همراه unique index روی request_key،
         * درخواست‌های همزمان و دوبار کلیک را کنترل می‌کند.
         */
        $transfer = WalletTransfer::query()->firstOrCreate(
            [
                'request_key' => $requestKey,
            ],
            [
                'sender_user_id' => $data->sender->getKey(),
                'recipient_user_id' => $recipient->getKey(),

                'sender_wallet_id' => $senderWallet->getKey(),
                'recipient_wallet_id' => $recipientWallet->getKey(),

                'amount' => $data->amount,

                'status' => WalletTransferStatusEnum::PENDING,

                'description' => 'انتقال موجودی بین کاربران',

                'metadata' => array_merge(
                    $data->metadata,
                    [
                        'source' => 'user_wallet_transfer',
                        'initiated_by_user_id' => $data->sender->getKey(),
                    ],
                ),
            ],
        );

        /*
         * جلوگیری از استفاده یک request_key برای انتقالی متفاوت.
         */
        $this->assertTransferMatchesRequest(
            transfer: $transfer,
            sender: $data->sender,
            recipient: $recipient,
            senderWallet: $senderWallet,
            recipientWallet: $recipientWallet,
            amount: $data->amount,
        );

        if ($transfer->status === WalletTransferStatusEnum::COMPLETED) {
            return $this->loadTransferRelations($transfer);
        }

        if ($transfer->status === WalletTransferStatusEnum::FAILED) {
            throw WalletTransferException::transferAlreadyFailed();
        }

        try {
            return DB::transaction(function () use (
                $data,
                $transfer,
                $requestKey,
            ): WalletTransfer {
                /** @var WalletTransfer $lockedTransfer */
                $lockedTransfer = WalletTransfer::query()
                    ->whereKey($transfer->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                 * درخواست همزمان ممکن است منتظر قفل بماند و پس از
                 * تکمیل درخواست اول به این بخش برسد.
                 */
                if ($lockedTransfer->status === WalletTransferStatusEnum::COMPLETED) {
                    return $this->loadTransferRelations(
                        $lockedTransfer,
                    );
                }

                if ($lockedTransfer->status === WalletTransferStatusEnum::FAILED) {
                    throw WalletTransferException::transferAlreadyFailed();
                }

                $lockedWallets = $this->lockWalletsInStableOrder(
                    senderWalletId: (int)$lockedTransfer->sender_wallet_id,
                    recipientWalletId: (int)$lockedTransfer->recipient_wallet_id,
                );

                /** @var Wallet|null $lockedSenderWallet */
                $lockedSenderWallet = $lockedWallets->get(
                    (int)$lockedTransfer->sender_wallet_id,
                );

                /** @var Wallet|null $lockedRecipientWallet */
                $lockedRecipientWallet = $lockedWallets->get(
                    (int)$lockedTransfer->recipient_wallet_id,
                );

                if (!$lockedSenderWallet || !$lockedSenderWallet->allowsUserTransactions()) {
                    throw WalletTransferException::senderWalletUnavailable();
                }

                if (!$lockedRecipientWallet || !$lockedRecipientWallet->allowsUserTransactions()) {
                    throw WalletTransferException::recipientWalletUnavailable();
                }

                /*
                 * انتقال فقط از موجودی قابل برداشت انجام می‌شود.
                 */
                if ((int)$lockedSenderWallet->available_withdrawable_balance < (int)$lockedTransfer->amount) {
                    throw WalletTransferException::insufficientWithdrawableBalance();
                }

                $debitTransaction = $this->debitWalletAction->execute(
                    new DebitWalletDTO(
                        wallet: $lockedSenderWallet,

                        type: WalletTransactionTypeEnum::TRANSFER_OUT,

                        amount: (int)$lockedTransfer->amount,

                        idempotencyKey: $this->debitIdempotencyKey(
                            $requestKey,
                        ),

                        policy: WalletDebitPolicyEnum::WITHDRAWABLE_ONLY,

                        actor: $data->sender,

                        reference: $lockedTransfer,

                        description: 'انتقال موجودی به کاربر دیگر',

                        metadata: array_merge(
                            $data->metadata,
                            [
                                'wallet_transfer_id' => $lockedTransfer->getKey(),
                                'wallet_transfer_token' => $lockedTransfer->public_token,
                                'counterparty_user_id' => $lockedTransfer->recipient_user_id,
                            ],
                        ),
                    ),
                );

                /*
                 * WITHDRAWABLE_ONLY باید کل مبلغ را از موجودی قابل
                 * برداشت کم کرده باشد.
                 */
                if ((int)$debitTransaction->withdrawable_amount !== (int)$lockedTransfer->amount) {
                    throw new \LogicException(
                        'Wallet transfer debit must be fully withdrawable.'
                    );
                }

                $creditTransaction = $this->creditWalletAction->execute(
                    new CreditWalletDTO(
                        wallet: $lockedRecipientWallet,

                        type: WalletTransactionTypeEnum::TRANSFER_IN,

                        amount: (int)$lockedTransfer->amount,

                        idempotencyKey: $this->creditIdempotencyKey(
                            $requestKey,
                        ),

                        /*
                         * موجودی منتقل‌شده برای گیرنده نیز قابل برداشت است.
                         */
                        withdrawableAmount: (int)$debitTransaction->withdrawable_amount,

                        actor: $data->sender,

                        reference: $lockedTransfer,

                        description: 'دریافت موجودی از کاربر دیگر',

                        metadata: array_merge(
                            $data->metadata,
                            [
                                'wallet_transfer_id' => $lockedTransfer->getKey(),

                                'wallet_transfer_token' => $lockedTransfer->public_token,

                                'counterparty_user_id' => $lockedTransfer->sender_user_id,
                            ],
                        ),
                    ),
                );

                $lockedTransfer->forceFill([
                    'status' => WalletTransferStatusEnum::COMPLETED,
                    'debit_transaction_id' => $debitTransaction->getKey(),
                    'credit_transaction_id' => $creditTransaction->getKey(),
                    'completed_at' => now(),
                    'failed_at' => null,
                    'failure_reason' => null,
                ])->save();

                return $this->loadTransferRelations(
                    $lockedTransfer,
                );
            }, 3);
        } catch (Throwable $exception) {
            /*
             * برداشت و واریز داخل تراکنش اصلی rollback شده‌اند.
             * فقط وضعیت عملیات اصلی را ناموفق می‌کنیم.
             */
            $this->markTransferAsFailed(
                transfer: $transfer,
                exception: $exception,
            );

            throw $exception;
        }
    }

    private function validate(
        TransferWalletBalanceDTO $data,
    ): void
    {
        if (!$data->sender->exists) {
            throw new InvalidArgumentException(
                'Wallet transfer sender must exist.'
            );
        }

        if ($data->amount <= 0) {
            throw new InvalidArgumentException(
                'Wallet transfer amount must be greater than zero.'
            );
        }

        $requestKey = trim($data->requestKey);

        if ($requestKey === '') {
            throw new InvalidArgumentException(
                'Wallet transfer request key is required.'
            );
        }

        /*
         * برای افزودن پسوندهای :debit و :credit فضای کافی باقی می‌گذاریم.
         */
        if (Str::length($requestKey) > 150) {
            throw new InvalidArgumentException(
                'Wallet transfer request key cannot exceed 150 characters.'
            );
        }
    }

    private function normalizeIranianMobile(string $mobile): string
    {
        $mobile = strtr($mobile, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',

            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);

        $mobile = preg_replace('/\D+/', '', trim($mobile)) ?? '';

        if (str_starts_with($mobile, '0098')) {
            $mobile = substr($mobile, 4);
        } elseif (str_starts_with($mobile, '98')) {
            $mobile = substr($mobile, 2);
        }

        if (strlen($mobile) === 10 && str_starts_with($mobile, '9')) {
            $mobile = '0' . $mobile;
        }

        if (strlen($mobile) !== 11 || !preg_match('/^09\d{9}$/', $mobile)) {
            throw WalletTransferException::invalidRecipientMobile();
        }

        return $mobile;
    }

    /**
     * قفل کردن کیف پول‌ها با ترتیب ثابت، احتمال deadlock را کاهش می‌دهد.
     *
     * @return Collection<int, Wallet>
     */
    private function lockWalletsInStableOrder(
        int $senderWalletId,
        int $recipientWalletId,
    ): Collection
    {
        return Wallet::query()
            ->whereIn('id', [
                $senderWalletId,
                $recipientWalletId,
            ])
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    private function assertTransferMatchesRequest(
        WalletTransfer $transfer,
        User           $sender,
        User           $recipient,
        Wallet         $senderWallet,
        Wallet         $recipientWallet,
        int            $amount,
    ): void
    {
        $matches = (int)$transfer->sender_user_id === (int)$sender->getKey()
            && (int)$transfer->recipient_user_id === (int)$recipient->getKey()
            && (int)$transfer->sender_wallet_id === (int)$senderWallet->getKey()
            && (int)$transfer->recipient_wallet_id === (int)$recipientWallet->getKey()
            && (int)$transfer->amount === $amount;
        if (!$matches) {
            throw WalletTransferException::requestConflict();
        }
    }

    private function debitIdempotencyKey(string $requestKey): string
    {
        return $requestKey . ':debit';
    }

    private function creditIdempotencyKey(string $requestKey): string
    {
        return $requestKey . ':credit';
    }

    private function markTransferAsFailed(
        WalletTransfer $transfer,
        Throwable      $exception,
    ): void
    {
        $failureReason = $exception instanceof WalletTransferException
            ? $exception->reason
            : 'unexpected_error';

        try {
            WalletTransfer::query()
                ->whereKey($transfer->getKey())
                ->where(
                    'status',
                    WalletTransferStatusEnum::PENDING->value,
                )
                ->update([
                    'status' => WalletTransferStatusEnum::FAILED->value,

                    'failed_at' => now(),

                    /*
                     * پیام خام Exception ذخیره نمی‌شود تا اطلاعات
                     * داخلی یا حساس وارد دیتابیس نشود.
                     */
                    'failure_reason' => $failureReason,

                    'updated_at' => now(),
                ]);
        } catch (Throwable $markFailedException) {
            report($markFailedException);
        }
    }

    private function loadTransferRelations(
        WalletTransfer $transfer,
    ): WalletTransfer
    {
        return $transfer->loadMissing([
            'sender',
            'recipient',

            'senderWallet',
            'recipientWallet',

            'debitTransaction',
            'creditTransaction',
        ]);
    }
}
