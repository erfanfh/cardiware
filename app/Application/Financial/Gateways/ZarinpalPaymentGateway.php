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

class ZarinpalPaymentGateway implements PaymentGateway
{
    public function gateway(): PaymentGatewayEnum
    {
        return PaymentGatewayEnum::ZARINPAL;
    }

    public function start(StartPaymentRequestDTO $request): StartPaymentResultDTO
    {
        $payload = [
            'merchant_id' => $this->merchantId(),
            'amount' => $this->toGatewayAmount($request->payment->amount),
            'callback_url' => $request->callbackUrl,
            'description' => $this->description($request),
            'metadata' => $this->metadata($request),
        ];

        try {
            $response = Http::timeout($this->timeout())
                ->acceptJson()
                ->asJson()
                ->post($this->requestUrl(), $payload);

            $data = $response->json() ?? [];

            $code = (int)data_get($data, 'data.code');
            $authority = data_get($data, 'data.authority');

            if (!$response->successful() || $code !== 100 || !$authority) {
                return new StartPaymentResultDTO(
                    successful: false,
                    gatewayReference: $authority ? (string)$authority : null,
                    redirectUrl: null,
                    gatewayStatus: (string)$code,
                    gatewayMessage: $this->messageFromResponse($data, 'Zarinpal payment request failed.'),
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
                gatewayReference: (string)$authority,
                redirectUrl: $this->startUrl() . $authority,
                gatewayStatus: (string)$code,
                gatewayMessage: $this->messageFromResponse($data, 'Zarinpal payment request created successfully.'),
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
                gatewayMessage: 'Zarinpal payment request exception.',
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
        $authority = data_get($request->callbackPayload, 'Authority')
            ?? data_get($request->callbackPayload, 'authority')
            ?? $request->payment->gateway_reference;

        $callbackStatus = data_get($request->callbackPayload, 'Status')
            ?? data_get($request->callbackPayload, 'status');

        $payload = [
            'merchant_id' => $this->merchantId(),
            'amount' => $this->toGatewayAmount($request->payment->amount),
            'authority' => $authority,
        ];

        try {
            if (!$authority) {
                return new VerifyPaymentResultDTO(
                    successful: false,
                    gatewayTransactionId: null,
                    gatewayStatus: 'missing_authority',
                    gatewayMessage: 'Zarinpal authority is missing.',
                    rawResponse: [
                        'request' => $payload,
                        'callback_payload' => $request->callbackPayload,
                        'sandbox' => $this->isSandbox(),
                    ],
                );
            }

            /**
             * Zarinpal sends Status=NOK when user cancels or payment fails.
             * In that case we should not call verify.
             */
            if ($callbackStatus !== null && strtoupper((string)$callbackStatus) !== 'OK') {
                return new VerifyPaymentResultDTO(
                    successful: false,
                    gatewayTransactionId: null,
                    gatewayStatus: (string)$callbackStatus,
                    gatewayMessage: 'Zarinpal callback status is not OK.',
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
                ->post($this->verifyUrl(), $payload);

            $data = $response->json() ?? [];

            $code = (int)data_get($data, 'data.code');

            /**
             * 100 = Verified
             * 101 = Already verified
             */
            $successful = $response->successful() && in_array($code, [100, 101], true);

            if (!$successful) {
                return new VerifyPaymentResultDTO(
                    successful: false,
                    gatewayTransactionId: data_get($data, 'data.ref_id') ? (string)data_get($data, 'data.ref_id') : null,
                    gatewayStatus: (string)$code,
                    gatewayMessage: $this->messageFromResponse($data, 'Zarinpal payment verification failed.'),
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
                gatewayTransactionId: (string)(
                    data_get($data, 'data.ref_id')
                    ?? $authority
                ),
                gatewayStatus: (string)$code,
                gatewayMessage: $this->messageFromResponse($data, 'Zarinpal payment verified successfully.'),
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
                gatewayMessage: 'Zarinpal payment verification exception.',
                rawResponse: [
                    'request' => $payload,
                    'callback_payload' => $request->callbackPayload,
                    'exception' => $exception->getMessage(),
                    'sandbox' => $this->isSandbox(),
                ],
            );
        }
    }

    private function merchantId(): string
    {
        return (string)config('financial.gateways.zarinpal.merchant_id');
    }

    private function requestUrl(): string
    {
        return $this->gatewayConfig('request_url');
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

        return (string)config("financial.gateways.zarinpal.{$mode}.{$key}");
    }

    private function isSandbox(): bool
    {
        return (bool)config('financial.gateways.zarinpal.sandbox', true);
    }

    private function timeout(): int
    {
        return (int)config('financial.gateways.zarinpal.timeout', 20);
    }

    private function toGatewayAmount(int $amountInToman): int
    {
        return $amountInToman * (int)config('financial.gateways.zarinpal.amount_multiplier', 10);
    }

    private function description(StartPaymentRequestDTO $request): string
    {
        return data_get($request->metadata, 'description')
            ?? data_get($request->metadata, 'order_number')
            ?? 'پرداخت سفارش';
    }

    /**
     * @return array<string, mixed>
     */
    private function metadata(StartPaymentRequestDTO $request): array
    {
        return array_filter([
            'mobile' => data_get($request->metadata, 'mobile'),
            'email' => data_get($request->metadata, 'email'),
            'order_id' => data_get($request->metadata, 'order_number')
                ?? data_get($request->metadata, 'order_id')
                    ?? $request->payment->tracking_code,
        ], fn($value) => $value !== null && $value !== '');
    }

    /**
     * @param array<string, mixed> $data
     */
    private function messageFromResponse(array $data, string $default): string
    {
        return (string)(
            data_get($data, 'data.message')
            ?? data_get($data, 'errors.message')
            ?? data_get($data, 'errors.0.message')
            ?? $default
        );
    }
}
