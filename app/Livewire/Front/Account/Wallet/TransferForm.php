<?php

namespace App\Livewire\Front\Account\Wallet;

use App\Application\Financial\Actions\Wallet\TransferWalletBalanceAction;
use App\Application\Financial\DTOs\TransferWalletBalanceDTO;
use App\Application\Financial\Exceptions\WalletTransferException;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Identity\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class TransferForm extends Component
{
    public string $recipientMobile = '';

    public string $amount = '';

    #[Locked]
    public string $requestKey = '';

    public ?string $successMessage = null;

    /**
     * مبالغ پیشنهادی برحسب تومان.
     *
     * @var array<int>
     */
    #[Locked]
    public array $suggestedAmounts = [];

    public function mount(): void
    {
        $this->requestKey = $this->generateRequestKey();

        $this->suggestedAmounts = array_values(
            array_filter(
                array_map(
                    static fn(mixed $amount): int => (int)$amount,
                    config(
                        'financial.wallet.transfer.suggested_amounts',
                        [
                            100_000,
                            200_000,
                            500_000,
                            1_000_000,
                        ],
                    ),
                ),
                static fn(int $amount): bool => $amount > 0,
            ),
        );
    }

    /**
     * انتقال موجودی به کاربر مقصد.
     */
    public function submit(
        mixed                       $submittedMobile,
        mixed                       $submittedAmount,
        TransferWalletBalanceAction $transferWalletBalanceAction,
    ): void
    {
        $this->resetErrorBag();

        $this->successMessage = null;

        /*
         * مقادیر مستقیما از Alpine دریافت می‌شوند تا در صورت کلیک سریع،
         * آخرین مقدار قابل مشاهده در ورودی به سرور ارسال شود.
         */
        $this->recipientMobile = is_scalar($submittedMobile)
            ? trim((string)$submittedMobile)
            : '';

        $this->amount = $this->normalizeAmount(
            is_scalar($submittedAmount)
                ? (string)$submittedAmount
                : '',
        );

        $validated = $this->validate();

        /** @var User|null $user */
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        $this->ensureRateLimitIsNotExceeded($user);

        try {
            $transfer = $transferWalletBalanceAction->execute(
                new TransferWalletBalanceDTO(
                    sender: $user,

                    recipientMobile: $validated['recipientMobile'],

                    amount: (int)$validated['amount'],

                    requestKey: $this->requestKey,

                    metadata: [
                        'source' => 'account_wallet',
                        'sender_ip' => request()->ip(),
                        'user_agent' => Str::limit(
                            (string)request()->userAgent(),
                            500,
                        ),
                    ],
                ),
            );

            $recipientMobile = $transfer->recipient?->mobile ?: $validated['recipientMobile'];

            $transferredAmount = (int)$transfer->amount;

            $this->successMessage = sprintf(
                'مبلغ %s تومان با موفقیت به شماره %s انتقال داده شد.',
                number_format($transferredAmount),
                $recipientMobile,
            );

            /*
             * درخواست بعدی باید کلید جدید داشته باشد.
             */
            $this->requestKey = $this->generateRequestKey();

            $this->recipientMobile = '';
            $this->amount = '';

            /*
             * پاک کردن ورودی‌های Alpine بعد از انتقال موفق.
             */
            $this->dispatch('wallet-transfer-form-reset');

            /*
             * به‌روزرسانی موجودی نمایش داده‌شده در بالای صفحه.
             */
            $senderWallet = Wallet::query()
                ->where('user_id', $user->getKey())
                ->first();

            if ($senderWallet) {
                $this->dispatch(
                    'wallet-balance-updated',
                    availableBalance: (int)$senderWallet->available_balance,
                    availableWithdrawableBalance: (int)$senderWallet->available_withdrawable_balance,
                    hasHeldBalance: (int)$senderWallet->held_balance > 0,
                );
            }
        } catch (WalletTransferException $exception) {
            /*
             * انتقال ناموفق نباید با همان request key دوباره اجرا شود.
             */
            $this->requestKey = $this->generateRequestKey();

            $this->addError(
                $this->errorFieldForReason($exception->reason),
                $this->errorMessageForReason($exception->reason),
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            $this->requestKey = $this->generateRequestKey();

            $this->addError(
                'form',
                'در حال حاضر امکان انتقال موجودی وجود ندارد. کمی بعد دوباره تلاش کنید.',
            );
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'recipientMobile' => [
                'required',
                'string',
                'max:32',

                /*
                 * فرمت نهایی و معتبر بودن شماره داخل اکشن اصلی
                 * دوباره بررسی می‌شود.
                 */
                'regex:/^[0-9۰-۹٠-٩+\-\s()]+$/u',
            ],

            'amount' => [
                'required',
                'integer',
                'min:' . $this->minimumAmount(),
                'max:' . $this->maximumAmount(),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'recipientMobile.required' =>
                'شماره موبایل کاربر مقصد را وارد کنید.',

            'recipientMobile.string' =>
                'شماره موبایل واردشده معتبر نیست.',

            'recipientMobile.max' =>
                'شماره موبایل واردشده معتبر نیست.',

            'recipientMobile.regex' =>
                'شماره موبایل واردشده معتبر نیست.',

            'amount.required' =>
                'مبلغ انتقال را وارد کنید.',

            'amount.integer' =>
                'مبلغ انتقال باید یک عدد صحیح باشد.',

            'amount.min' =>
                'حداقل مبلغ انتقال :min تومان است.',

            'amount.max' =>
                'حداکثر مبلغ انتقال :max تومان است.',
        ];
    }

    private function minimumAmount(): int
    {
        return (int)config(
            'financial.wallet.transfer.minimum_amount',
            10_000,
        );
    }

    private function maximumAmount(): int
    {
        return (int)config(
            'financial.wallet.transfer.maximum_amount',
            20_000_000,
        );
    }

    private function normalizeAmount(string $amount): string
    {
        $amount = strtr($amount, [
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

        return str_replace(
            [
                ',',
                '٬',
                ' ',
                'تومان',
            ],
            '',
            trim($amount),
        );
    }

    private function ensureRateLimitIsNotExceeded(
        User $user,
    ): void
    {
        $key = 'wallet-transfer:submit:' . $user->getKey();

        $maxAttempts = (int)config(
            'financial.wallet.transfer.rate_limit.max_attempts',
            5,
        );

        $decaySeconds = (int)config(
            'financial.wallet.transfer.rate_limit.decay_seconds',
            60,
        );

        if (
            RateLimiter::tooManyAttempts(
                key: $key,
                maxAttempts: $maxAttempts,
            )
        ) {
            $availableIn = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'form' =>
                    "تعداد درخواست‌ها زیاد است. {$availableIn} ثانیه دیگر دوباره تلاش کنید.",
            ]);
        }

        RateLimiter::hit(
            key: $key,
            decaySeconds: $decaySeconds,
        );
    }

    private function errorFieldForReason(
        string $reason,
    ): string
    {
        return match ($reason) {
            'invalid_recipient_mobile',
            'recipient_not_found',
            'cannot_transfer_to_self',
            'recipient_wallet_unavailable' => 'recipientMobile',

            'insufficient_withdrawable_balance' => 'amount',

            default => 'form',
        };
    }

    private function errorMessageForReason(
        string $reason,
    ): string
    {
        return match ($reason) {
            'invalid_recipient_mobile' =>
            'شماره موبایل واردشده معتبر نیست.',

            'recipient_not_found' =>
            'کاربری با این شماره موبایل پیدا نشد.',

            'cannot_transfer_to_self' =>
            'امکان انتقال موجودی به شماره موبایل خودتان وجود ندارد.',

            'sender_wallet_unavailable' =>
            'کیف پول شما برای انتقال موجودی فعال نیست.',

            'recipient_wallet_unavailable' =>
            'کیف پول کاربر مقصد فعال نیست یا امکان دریافت انتقال را ندارد.',

            'insufficient_withdrawable_balance' =>
            'موجودی قابل برداشت شما برای این انتقال کافی نیست.',

            'request_conflict' =>
            'اطلاعات درخواست با عملیات قبلی مطابقت ندارد. دوباره تلاش کنید.',

            'transfer_already_failed' =>
            'درخواست قبلی ناموفق بوده است. دوباره انتقال را ثبت کنید.',

            default =>
            'امکان انجام این انتقال وجود ندارد.',
        };
    }

    private function generateRequestKey(): string
    {
        return (string)Str::uuid();
    }

    public function render(): View
    {
        /** @var User|null $user */
        $user = auth()->user();

        $wallet = $user
            ? Wallet::query()
                ->where('user_id', $user->getKey())
                ->first()
            : null;

        return view(
            'livewire.front.account.wallet.transfer-form',
            [
                'walletAvailable' =>
                    $wallet?->allowsUserTransactions() === true,

                'availableWithdrawableBalance' =>
                    (int)($wallet?->available_withdrawable_balance ?? 0),

                'minimumAmount' =>
                    $this->minimumAmount(),

                'maximumAmount' =>
                    $this->maximumAmount(),
            ],
        );
    }
}
