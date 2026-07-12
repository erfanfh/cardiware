<?php

namespace App\Livewire\Front\Account\Wallet;

use App\Application\Financial\Actions\Wallet\CreateWalletTopUpAction;
use App\Application\Financial\Actions\Wallet\StartWalletTopUpPaymentAction;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Throwable;

class TopUpForm extends Component
{
    public string $amount = '';

    public string $gateway = '';

    public string $requestKey = '';

    /**
     * مبالغ پیشنهادی برحسب تومان.
     *
     * @var array<int>
     */
    public array $suggestedAmounts = [
        100_000,
        200_000,
        500_000,
        1_000_000,
    ];

    public function mount(): void
    {
        $this->requestKey = $this->generateRequestKey();

        $this->gateway = $this->allowedGatewayValues()[0]
            ?? '';
    }

    /**
     * انتخاب مبلغ پیشنهادی.
     */
    public function selectAmount(int $amount): void
    {
        $this->amount = (string)$amount;

        $this->resetValidation('amount');
    }

    /**
     * ایجاد درخواست شارژ و انتقال به درگاه.
     */
    public function submit(
        CreateWalletTopUpAction       $createWalletTopUpAction,
        StartWalletTopUpPaymentAction $startWalletTopUpPaymentAction,
    )
    {
        $this->amount = $this->normalizeAmount($this->amount);

        $validated = $this->validate();

        /** @var User $user */
        $user = auth()->user();

        $this->ensureRateLimitIsNotExceeded($user);

        $gateway = PaymentGatewayEnum::tryFrom(
            $validated['gateway'],
        );

        if (!$gateway || !$gateway->isExternal()) {
            throw ValidationException::withMessages([
                'gateway' => 'درگاه پرداخت انتخاب‌شده معتبر نیست.',
            ]);
        }

        try {
            $topUp = $createWalletTopUpAction->execute(
                user: $user,
                amount: (int)$validated['amount'],
                requestKey: $this->requestKey,
                metadata: [
                    'source' => 'account_wallet',
                    'user_id' => $user->id,
                    'mobile' => $user->mobile,
                    'email' => $user->email,
                ],
            );

            $payment = $startWalletTopUpPaymentAction->execute(
                topUp: $topUp,
                gateway: $gateway,
                callbackUrl: route('payment.callback', [
                    'gateway' => $gateway->value,
                ]),
                metadata: [
                    'user_id' => $user->id,
                    'mobile' => $user->mobile,
                    'email' => $user->email,

                    'description' => 'شارژ کیف پول',

                    'wallet_top_up_id' => $topUp->id,
                    'wallet_top_up_token' => $topUp->public_token,
                ],
            );

            if ($payment->status === PaymentStatusEnum::FAILED) {
                /*
                 * Retry باید TopUp و Payment جدید بسازد.
                 */
                $this->requestKey = $this->generateRequestKey();

                $this->addError(
                    'gateway',
                    $payment->gateway_message
                        ?: 'اتصال به درگاه پرداخت انجام نشد. دوباره تلاش کنید.',
                );

                return null;
            }

            if ($payment->status !== PaymentStatusEnum::PENDING) {
                $this->addError(
                    'gateway',
                    'پرداخت در وضعیت قابل انتقال به درگاه قرار ندارد.',
                );

                return null;
            }

            $redirectUrl = data_get(
                $payment->request_payload,
                'redirect_url',
            );

            if (blank($redirectUrl)) {
                report(new \RuntimeException(
                    "Redirect URL is missing for payment [{$payment->id}]."
                ));

                $this->addError(
                    'gateway',
                    'آدرس انتقال به درگاه دریافت نشد. لطفاً دوباره تلاش کنید.',
                );

                return null;
            }

            return redirect()->away($redirectUrl);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            $this->addError(
                'form',
                'در حال حاضر امکان شروع شارژ کیف پول وجود ندارد. کمی بعد دوباره تلاش کنید.',
            );

            return null;
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        $minimumAmount = (int)config(
            'financial.wallet.top_up.minimum_amount',
            50_000,
        );

        $maximumAmount = (int)config(
            'financial.wallet.top_up.maximum_amount',
            20_000_000,
        );

        return [
            'amount' => [
                'required',
                'integer',
                'min:' . $minimumAmount,
                'max:' . $maximumAmount,
            ],

            'gateway' => [
                'required',
                'string',
                Rule::in($this->allowedGatewayValues()),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'amount.required' => 'مبلغ شارژ را وارد کنید.',
            'amount.integer' => 'مبلغ شارژ باید یک عدد صحیح باشد.',
            'amount.min' => 'حداقل مبلغ شارژ :min تومان است.',
            'amount.max' => 'حداکثر مبلغ شارژ :max تومان است.',

            'gateway.required' => 'درگاه پرداخت را انتخاب کنید.',
            'gateway.in' => 'درگاه پرداخت انتخاب‌شده معتبر نیست.',
        ];
    }

    /**
     * @return array<int, PaymentGatewayEnum>
     */
    public function allowedGateways(): array
    {
        return collect(
            config('financial.wallet.top_up.gateways', []),
        )
            ->map(
                fn(mixed $gateway): ?PaymentGatewayEnum => is_string($gateway)
                    ? PaymentGatewayEnum::tryFrom($gateway)
                    : null
            )
            ->filter(
                fn(?PaymentGatewayEnum $gateway): bool => $gateway?->isExternal() === true
            )
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function allowedGatewayValues(): array
    {
        return array_map(
            fn(PaymentGatewayEnum $gateway): string => $gateway->value,
            $this->allowedGateways(),
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
            [',', '٬', ' ', 'تومان'],
            '',
            trim($amount),
        );
    }

    private function ensureRateLimitIsNotExceeded(User $user): void
    {
        $key = 'wallet-top-up:start:' . $user->id;

        $maxAttempts = (int)config(
            'financial.wallet.top_up.rate_limit.max_attempts',
            5,
        );

        $decaySeconds = (int)config(
            'financial.wallet.top_up.rate_limit.decay_seconds',
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
                'form' => "تعداد درخواست‌ها زیاد است. {$availableIn} ثانیه دیگر دوباره تلاش کنید.",
            ]);
        }

        RateLimiter::hit(
            key: $key,
            decaySeconds: $decaySeconds,
        );
    }

    private function generateRequestKey(): string
    {
        return (string)Str::uuid();
    }

    public function render(): View
    {
        return view(
            'livewire.front.account.wallet.top-up-form',
            [
                'gateways' => $this->allowedGateways(),

                'minimumAmount' => (int)config(
                    'financial.wallet.top_up.minimum_amount',
                    50_000,
                ),

                'maximumAmount' => (int)config(
                    'financial.wallet.top_up.maximum_amount',
                    20_000_000,
                ),
            ],
        );
    }
}
