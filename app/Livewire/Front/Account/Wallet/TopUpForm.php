<?php

namespace App\Livewire\Front\Account\Wallet;

use App\Application\Financial\Actions\Payment\ResolveDefaultPaymentGatewayAction;
use App\Application\Financial\Actions\Wallet\CreateWalletTopUpAction;
use App\Application\Financial\Actions\Wallet\StartWalletTopUpPaymentAction;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class TopUpForm extends Component
{
    public string $amount = '';

    #[Locked]
    public string $requestKey = '';

    public array $suggestedAmounts = [
        100_000,
        200_000,
        500_000,
        1_000_000,
    ];

    public function mount(): void
    {
        $this->requestKey = $this->generateRequestKey();
    }

    public function submit(
        mixed                         $submittedAmount,
        CreateWalletTopUpAction       $createWalletTopUpAction,
        StartWalletTopUpPaymentAction $startWalletTopUpPaymentAction,
    )
    {
        $this->amount = $this->normalizeAmount(is_scalar($submittedAmount) ? (string)$submittedAmount : '');

        $validated = $this->validate();

        /** @var User $user */
        $user = auth()->user();

        $gateway = $this->defaultGateway();

        if (!$gateway) {
            throw ValidationException::withMessages([
                'form' => 'درگاه پیشفرض پرداخت به درستی تنظیم نشده است.',
            ]);
        }

        $this->ensureRateLimitIsNotExceeded($user);

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
                $this->requestKey = $this->generateRequestKey();

                $this->addError(
                    'form',
                    $payment->gateway_message ?: 'اتصال به درگاه پرداخت انجام نشد. دوباره تلاش کنید.',
                );

                return null;
            }

            if ($payment->status !== PaymentStatusEnum::PENDING) {
                $this->addError('form', 'پرداخت در وضعیت قابل انتقال به درگاه قرار ندارد.');

                return null;
            }

            $redirectUrl = data_get($payment->request_payload, 'redirect_url');

            if (blank($redirectUrl)) {
                report(new \RuntimeException(
                    "Redirect URL is missing for payment [{$payment->id}]."
                ));

                $this->addError(
                    'form',
                    'آدرس انتقال به درگاه دریافت نشد. دوباره تلاش کنید.',
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
        $minimumAmount = $this->minimumAmount();
        $maximumAmount = $this->maximumAmount();

        return [
            'amount' => [
                'required',
                'integer',
                'min:' . $minimumAmount,
                'max:' . $maximumAmount,
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
        ];
    }

    /**
     * درگاه پیشفرض سیستم را برمی گرداند.
     */
    private function defaultGateway(): ?PaymentGatewayEnum
    {
        $gateway = app(ResolveDefaultPaymentGatewayAction::class)->execute();

        if (!$gateway || !$gateway->isExternal()) {
            return null;
        }

        return $gateway;
    }

    private function minimumAmount(): int
    {
        return (int)config(
            'financial.wallet.top_up.minimum_amount',
            50_000,
        );
    }

    private function maximumAmount(): int
    {
        return (int)config(
            'financial.wallet.top_up.maximum_amount',
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
                'form' => "تعداد درخواست ها زیاد است. {$availableIn} ثانیه دیگر دوباره تلاش کنید.",
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
                'minimumAmount' => $this->minimumAmount(),
                'maximumAmount' => $this->maximumAmount(),

                'gatewayAvailable' => $this->defaultGateway() !== null,
            ],
        );
    }
}
