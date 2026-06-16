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

class PaypingPaymentGateway implements PaymentGateway
{
    public function gateway(): PaymentGatewayEnum
    {
        return PaymentGatewayEnum::PAYPING;
    }

    public function start(StartPaymentRequestDTO $request): StartPaymentResultDTO
    {
        $payload = [
            'amount' => $this->toGatewayAmount($request->payment->amount),
            'returnUrl' => $request->callbackUrl,
            'clientRefId' => $request->payment->tracking_code,
            'description' => $this->description($request),
        ];

        $payerName = data_get($request->metadata, 'payer_name');
        $payerIdentity = data_get($request->metadata, 'mobile')
            ?? data_get($request->metadata, 'email');

        if ($payerName) {
            $payload['payerName'] = $payerName;
        }

        if ($payerIdentity) {
            $payload['payerIdentity'] = $payerIdentity;
        }

        try {
            $response = Http::timeout($this->timeout())
                ->acceptJson()
                ->asJson()
                ->withToken($this->token())
                ->post($this->payUrl(), $payload);

            $data = $response->json() ?? [];

            /*
             * PayPing v3 create payment usually returns a payment code.
             * Some responses may return it as `code`, `Code`, or nested fields.
             */
            $code = data_get($data, 'code')
                ?? data_get($data, 'Code')
                ?? data_get($data, 'data.code')
                ?? data_get($data, 'data.Code');

            if (!$response->successful() || !$code) {
                return new StartPaymentResultDTO(
                    successful: false,
                    gatewayReference: $code ? (string)$code : null,
                    redirectUrl: null,
                    gatewayStatus: (string)$response->status(),
                    gatewayMessage: $this->messageFromResponse($data, 'PayPing payment request failed.'),
                    rawResponse: [
                        'request' => $payload,
                        'response' => $data,
                        'http_status' => $response->status(),
                        'sandbox' => $this->isSandbox(),
                    ],
                );
            }

            return new StartPaymentResultDTO(
                successful: true,
                gatewayReference: (string)$code,
                redirectUrl: $this->startUrl() . $code,
                gatewayStatus: (string)$response->status(),
                gatewayMessage: $this->messageFromResponse($data, 'PayPing payment request created successfully.'),
                rawResponse: [
                    'request' => $payload,
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
                gatewayMessage: 'PayPing payment request exception.',
                rawResponse: [
                    'request' => $payload,
                    'exception' => $exception->getMessage(),
                    'sandbox' => $this->isSandbox(),
                ],
            );
        }
    }

    public function verify(VerifyPaymentRequestDTO $request): VerifyPaymentResultDTO
    {
        /*
         * PayPing callback usually returns a reference / refId.
         * We support multiple key names to make callback handling safer.
         */
        $refId = data_get($request->callbackPayload, 'refId')
            ?? data_get($request->callbackPayload, 'RefId')
            ?? data_get($request->callbackPayload, 'refid')
            ?? data_get($request->callbackPayload, 'referenceId')
            ?? data_get($request->callbackPayload, 'reference_id')
            ?? data_get($request->callbackPayload, 'code')
            ?? data_get($request->callbackPayload, 'Code')
            ?? $request->payment->gateway_reference;

        $payload = [
            'amount' => $this->toGatewayAmount($request->payment->amount),
            'refId' => $refId,
        ];

        try {
            if (!$refId) {
                return new VerifyPaymentResultDTO(
                    successful: false,
                    gatewayTransactionId: null,
                    gatewayStatus: 'missing_ref_id',
                    gatewayMessage: 'PayPing refId is missing.',
                    rawResponse: [
                        'request' => $payload,
                        'callback_payload' => $request->callbackPayload,
                        'sandbox' => $this->isSandbox(),
                    ],
                );
            }

            $response = Http::timeout($this->timeout())
                ->acceptJson()
                ->asJson()
                ->withToken($this->token())
                ->post($this->verifyUrl(), $payload);

            $data = $response->json() ?? [];

            /*
             * PayPing verify commonly returns success with HTTP 200.
             * Depending on response shape, success may be indicated by status/code.
             * We accept successful HTTP response unless explicit failure is present.
             */
            $explicitCode = data_get($data, 'code')
                ?? data_get($data, 'Code')
                ?? data_get($data, 'status')
                ?? data_get($data, 'Status')
                ?? data_get($data, 'result')
                ?? data_get($data, 'Result');

            $successful = $response->successful();

            if ($explicitCode !== null) {
                $successful = $successful && in_array((string)$explicitCode, [
                        '0',
                        '1',
                        '100',
                        '200',
                        'success',
                        'SUCCESS',
                        'paid',
                        'PAID',
                    ], true);
            }

            if (!$successful) {
                return new VerifyPaymentResultDTO(
                    successful: false,
                    gatewayTransactionId: $refId ? (string)$refId : null,
                    gatewayStatus: (string)($explicitCode ?? $response->status()),
                    gatewayMessage: $this->messageFromResponse($data, 'PayPing payment verification failed.'),
                    rawResponse: [
                        'request' => $payload,
                        'response' => $data,
                        'callback_payload' => $request->callbackPayload,
                        'http_status' => $response->status(),
                        'sandbox' => $this->isSandbox(),
                    ],
                );
            }

            return new VerifyPaymentResultDTO(
                successful: true,
                gatewayTransactionId: (string)$refId,
                gatewayStatus: (string)($explicitCode ?? $response->status()),
                gatewayMessage: $this->messageFromResponse($data, 'PayPing payment verified successfully.'),
                rawResponse: [
                    'request' => $payload,
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
                gatewayMessage: 'PayPing payment verification exception.',
                rawResponse: [
                    'request' => $payload,
                    'callback_payload' => $request->callbackPayload,
                    'exception' => $exception->getMessage(),
                    'sandbox' => $this->isSandbox(),
                ],
            );
        }
    }

    private function token(): string
    {
        return get_setting('general', 'payping_token');
    }

    private function payUrl(): string
    {
        return $this->gatewayConfig('pay_url');
    }

    private function verifyUrl(): string
    {
        return $this->gatewayConfig('verify_url');
    }

    private function startUrl(): string
    {
        return rtrim($this->gatewayConfig('start_url'), '/') . '/';
    }

    private function gatewayConfig(string $key): string
    {
        $mode = $this->isSandbox() ? 'sandbox_urls' : 'production';

        return (string)config("financial.gateways.payping.{$mode}.{$key}");
    }

    private function isSandbox(): bool
    {
        return get_setting('general', 'payping_sandbox', false);
    }

    private function timeout(): int
    {
        return (int)config('financial.gateways.payping.timeout', 20);
    }

    private function toGatewayAmount(int $amountInToman): int
    {
        return $amountInToman * (int)config('financial.gateways.payping.amount_multiplier', 1);
    }

    private function description(StartPaymentRequestDTO $request): string
    {
        return data_get($request->metadata, 'description')
            ?? data_get($request->metadata, 'order_number')
            ?? 'پرداخت سفارش';
    }

    /**
     * @param array<string, mixed> $data
     */
    private function messageFromResponse(array $data, string $default): string
    {
        return (string)(
            data_get($data, 'message')
            ?? data_get($data, 'Message')
            ?? data_get($data, 'error')
            ?? data_get($data, 'Error')
            ?? data_get($data, 'errors.message')
            ?? data_get($data, 'errors.0.message')
            ?? $default
        );
    }
}
