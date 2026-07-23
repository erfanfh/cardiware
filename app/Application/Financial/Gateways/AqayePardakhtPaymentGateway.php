<?php

namespace App\Application\Financial\Gateways;

use App\Application\Financial\Contracts\PaymentGateway;
use App\Application\Financial\DTOs\StartPaymentRequestDTO;
use App\Application\Financial\DTOs\StartPaymentResultDTO;
use App\Application\Financial\DTOs\VerifyPaymentRequestDTO;
use App\Application\Financial\DTOs\VerifyPaymentResultDTO;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use Illuminate\Support\Facades\Http;
use Throwable;

class AqayePardakhtPaymentGateway implements PaymentGateway
{
    public function gateway(): PaymentGatewayEnum
    {
        return PaymentGatewayEnum::AQAYEPARDAKHT;
    }

    public function start(StartPaymentRequestDTO $request): StartPaymentResultDTO
    {
        $payload = array_filter([
            'pin' => $this->pin(),

            /*
             * آقای پرداخت مبلغ را برحسب تومان دریافت می‌کند.
             */
            'amount' => $request->payment->amount,

            'callback' => $request->callbackUrl,

            /*
             * طبق پیشنهاد مستندات آقای پرداخت، بازگشت را GET قرار می‌دهیم.
             */
            'callback_method' => 'GET',

            'invoice_id' => $request->payment->tracking_code,

            'mobile' => data_get($request->metadata, 'mobile'),

            'email' => data_get($request->metadata, 'email'),

            'card_number' => data_get($request->metadata, 'card_number'),

            'description' => $this->description($request),
        ], static fn(mixed $value): bool => $value !== null && $value !== '');

        try {
            $response = Http::timeout($this->timeout())
                ->acceptJson()
                ->asJson()
                ->post($this->createUrl(), $payload);

            $data = $response->json() ?? [];

            $status = strtolower((string)data_get($data, 'status'));
            $code = data_get($data, 'code');
            $transId = data_get($data, 'transid');

            $successful = $response->successful()
                && $status === 'success'
                && filled($transId);

            if (!$successful) {
                return new StartPaymentResultDTO(
                    successful: false,
                    gatewayReference: filled($transId)
                        ? (string)$transId
                        : null,
                    redirectUrl: null,
                    gatewayStatus: $this->gatewayStatus(
                        data: $data,
                        fallback: (string)$response->status(),
                    ),
                    gatewayMessage: $this->messageFromResponse(
                        data: $data,
                        default: 'Aqaye Pardakht payment request failed.',
                    ),
                    rawResponse: [
                        'request' => $this->safePayload($payload),
                        'response' => $data,
                        'http_status' => $response->status(),
                        'sandbox' => $this->isSandbox(),
                    ],
                );
            }

            return new StartPaymentResultDTO(
                successful: true,
                gatewayReference: (string)$transId,
                redirectUrl: $this->paymentUrl((string)$transId),
                gatewayStatus: $status,
                gatewayMessage: $this->messageFromResponse(
                    data: $data,
                    default: 'Aqaye Pardakht payment request created successfully.',
                ),
                rawResponse: [
                    'request' => $this->safePayload($payload),
                    'response' => $data,
                    'http_status' => $response->status(),
                    'sandbox' => $this->isSandbox(),
                ],
            );
        } catch (Throwable $exception) {
            report($exception);

            return new StartPaymentResultDTO(
                successful: false,
                gatewayReference: null,
                redirectUrl: null,
                gatewayStatus: 'exception',
                gatewayMessage: 'Aqaye Pardakht payment request exception.',
                rawResponse: [
                    'request' => $this->safePayload($payload),
                    'exception' => $exception->getMessage(),
                    'sandbox' => $this->isSandbox(),
                ],
            );
        }
    }

    public function verify(VerifyPaymentRequestDTO $request): VerifyPaymentResultDTO
    {
        $transId = data_get($request->callbackPayload, 'transid')
            ?? data_get($request->callbackPayload, 'TransId')
            ?? $request->payment->gateway_reference;

        $callbackStatus = data_get($request->callbackPayload, 'status')
            ?? data_get($request->callbackPayload, 'Status');

        $payload = [
            'pin' => $this->pin(),

            /*
             * مبلغ باید دقیقا با مبلغ مرحله ایجاد تراکنش یکسان باشد.
             */
            'amount' => $request->payment->amount,

            'transid' => $transId,
        ];

        try {
            if (!$transId) {
                return new VerifyPaymentResultDTO(
                    successful: false,
                    gatewayTransactionId: null,
                    gatewayStatus: 'missing_transid',
                    gatewayMessage: 'Aqaye Pardakht transid is missing.',
                    rawResponse: [
                        'request' => $this->safePayload($payload),
                        'callback_payload' => $request->callbackPayload,
                        'sandbox' => $this->isSandbox(),
                    ],
                );
            }

            /*
             * آقای پرداخت در callback مقدار status=1 را برای پرداخت موفق
             * و status=0 را برای پرداخت ناموفق ارسال می‌کند.
             *
             * اگر status ارسال نشده باشد، همچنان verify را انجام می‌دهیم.
             */
            if (
                $callbackStatus !== null
                && (string)$callbackStatus !== '1'
            ) {
                return new VerifyPaymentResultDTO(
                    successful: false,
                    gatewayTransactionId: null,
                    gatewayStatus: (string)$callbackStatus,
                    gatewayMessage: 'Aqaye Pardakht callback status is not successful.',
                    rawResponse: [
                        'request' => $this->safePayload($payload),
                        'callback_payload' => $request->callbackPayload,
                        'sandbox' => $this->isSandbox(),
                    ],
                );
            }

            $response = Http::timeout($this->timeout())
                ->acceptJson()
                ->asJson()
                ->post($this->verifyUrl(), $payload);

            $data = $response->json() ?? [];

            $code = (int)data_get($data, 'code', 0);

            /*
             * 1 = پرداخت موفق
             * 2 = تراکنش قبلا وریفای و پرداخت شده است
             *
             * کد 2 را برای جلوگیری از خراب شدن callback تکراری،
             * موفق در نظر می‌گیریم.
             */
            $successful = (
                    $response->successful()
                    && $code === 1
                ) || $code === 2;

            $gatewayTransactionId = data_get(
                $request->callbackPayload,
                'tracking_number',
            )
                ?? data_get(
                $request->callbackPayload,
                'trackingNumber',
            )
                ?? $transId;

            if (!$successful) {
                return new VerifyPaymentResultDTO(
                    successful: false,
                    gatewayTransactionId: filled($gatewayTransactionId)
                        ? (string)$gatewayTransactionId
                        : null,
                    gatewayStatus: $this->gatewayStatus(
                        data: $data,
                        fallback: (string)$response->status(),
                    ),
                    gatewayMessage: $this->messageFromResponse(
                        data: $data,
                        default: 'Aqaye Pardakht payment verification failed.',
                    ),
                    rawResponse: [
                        'request' => $this->safePayload($payload),
                        'response' => $data,
                        'callback_payload' => $request->callbackPayload,
                        'http_status' => $response->status(),
                        'sandbox' => $this->isSandbox(),
                    ],
                );
            }

            return new VerifyPaymentResultDTO(
                successful: true,
                gatewayTransactionId: (string)$gatewayTransactionId,
                gatewayStatus: (string)$code,
                gatewayMessage: $code === 2
                    ? 'Aqaye Pardakht payment was already verified.'
                    : 'Aqaye Pardakht payment verified successfully.',
                rawResponse: [
                    'request' => $this->safePayload($payload),
                    'response' => $data,
                    'callback_payload' => $request->callbackPayload,
                    'http_status' => $response->status(),
                    'sandbox' => $this->isSandbox(),
                ],
            );
        } catch (Throwable $exception) {
            report($exception);

            return new VerifyPaymentResultDTO(
                successful: false,
                gatewayTransactionId: null,
                gatewayStatus: 'exception',
                gatewayMessage: 'Aqaye Pardakht payment verification exception.',
                rawResponse: [
                    'request' => $this->safePayload($payload),
                    'callback_payload' => $request->callbackPayload,
                    'exception' => $exception->getMessage(),
                    'sandbox' => $this->isSandbox(),
                ],
            );
        }
    }

    private function pin(): string
    {
        if ($this->isSandbox()) {
            return 'sandbox';
        }

        return (string)get_setting(
            'general',
            'aqayepardakht_pin',
            '',
        );
    }

    private function isSandbox(): bool
    {
        return (bool)get_setting(
            'general',
            'aqayepardakht_sandbox',
            false,
        );
    }

    private function createUrl(): string
    {
        return (string)config(
            'financial.gateways.aqayepardakht.create_url',
        );
    }

    private function verifyUrl(): string
    {
        return (string)config(
            'financial.gateways.aqayepardakht.verify_url',
        );
    }

    private function paymentUrl(string $transId): string
    {
        $configKey = $this->isSandbox()
            ? 'sandbox_start_url'
            : 'start_url';

        $baseUrl = (string)config(
            "financial.gateways.aqayepardakht.{$configKey}",
        );

        return rtrim($baseUrl, '/') . '/' . $transId;
    }

    private function timeout(): int
    {
        return (int)config(
            'financial.gateways.aqayepardakht.timeout',
            20,
        );
    }

    private function description(StartPaymentRequestDTO $request): string
    {
        return (string)(
            data_get($request->metadata, 'description')
            ?? data_get($request->metadata, 'order_number')
            ?? 'پرداخت سفارش'
        );
    }

    /**
     * پین درگاه نباید داخل دیتابیس یا لاگ ذخیره شود.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function safePayload(array $payload): array
    {
        if (array_key_exists('pin', $payload)) {
            $payload['pin'] = '***';
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function gatewayStatus(array $data, string $fallback): string
    {
        return (string)(
            data_get($data, 'code')
            ?? data_get($data, 'status')
            ?? $fallback
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function messageFromResponse(
        array  $data,
        string $default,
    ): string
    {
        $message = data_get($data, 'message')
            ?? data_get($data, 'statusMessage');

        if (filled($message)) {
            return (string)$message;
        }

        $code = data_get($data, 'code');

        if ($code === null || $code === '') {
            return $default;
        }

        return $this->messageForCode(
            code: (string)$code,
            default: $default,
        );
    }

    private function messageForCode(
        string $code,
        string $default,
    ): string
    {
        return match ($code) {
            '0' => 'Payment was not completed.',
            '1' => 'Payment completed successfully.',
            '2' => 'Payment has already been verified.',

            '-1' => 'Payment amount is required.',
            '-2' => 'Gateway pin is required.',
            '-3' => 'Callback URL is required.',
            '-4' => 'Payment amount must be numeric.',
            '-5' => 'Payment amount is outside the allowed range.',
            '-6' => 'Gateway pin is invalid.',
            '-7' => 'Transaction ID is required.',
            '-8' => 'Transaction was not found.',
            '-9' => 'Transaction does not belong to this gateway.',
            '-10' => 'Payment amount does not match the transaction.',
            '-11' => 'Gateway is inactive or awaiting approval.',
            '-12' => 'Merchant is not allowed to send this request.',
            '-13' => 'Card number must contain exactly 16 digits.',
            '-14' => 'Gateway is being used on another website.',
            '-15' => 'Callback domain does not match the approved domain.',
            '-16' => 'Request referrer is invalid or missing.',
            '-17' => 'Callback method must be GET or POST.',

            default => $default,
        };
    }
}
