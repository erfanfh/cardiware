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

class ZibalPaymentGateway implements PaymentGateway
{
    public function gateway(): PaymentGatewayEnum
    {
        return PaymentGatewayEnum::ZIBAL;
    }

    public function start(StartPaymentRequestDTO $request): StartPaymentResultDTO
    {
        $payload = [
            'merchant' => $this->merchant(),
            'amount' => $this->toRial($request->payment->amount),
            'callbackUrl' => $request->callbackUrl,
            'description' => $this->description($request),
            'orderId' => $request->payment->tracking_code,
        ];

        $mobile = data_get($request->metadata, 'mobile');

        if ($mobile) {
            $payload['mobile'] = $mobile;
        }

        try {
            $response = Http::timeout($this->timeout())
                ->acceptJson()
                ->asJson()
                ->post($this->requestUrl(), $payload);

            $data = $response->json() ?? [];

            $result = (int)data_get($data, 'result');
            $trackId = data_get($data, 'trackId');

            if (!$response->successful() || $result !== 100 || !$trackId) {
                return new StartPaymentResultDTO(
                    successful: false,
                    gatewayReference: $trackId ? (string)$trackId : null,
                    redirectUrl: null,
                    gatewayStatus: (string)$result,
                    gatewayMessage: data_get($data, 'message')
                    ?? data_get($data, 'statusMessage')
                    ?? 'Zibal payment request failed.',
                    rawResponse: [
                        'request' => $payload,
                        'response' => $data,
                        'http_status' => $response->status(),
                    ],
                );
            }

            return new StartPaymentResultDTO(
                successful: true,
                gatewayReference: (string)$trackId,
                redirectUrl: $this->startUrl() . $trackId,
                gatewayStatus: (string)$result,
                gatewayMessage: data_get($data, 'message')
                ?? data_get($data, 'statusMessage')
                ?? 'Zibal payment request created successfully.',
                rawResponse: [
                    'request' => $payload,
                    'response' => $data,
                    'http_status' => $response->status(),
                ],
            );
        } catch (Throwable $exception) {
            report($exception);

            return new StartPaymentResultDTO(
                successful: false,
                gatewayReference: null,
                redirectUrl: null,
                gatewayStatus: 'exception',
                gatewayMessage: 'Zibal payment request exception.',
                rawResponse: [
                    'request' => $payload,
                    'exception' => $exception->getMessage(),
                ],
            );
        }
    }

    public function verify(VerifyPaymentRequestDTO $request): VerifyPaymentResultDTO
    {
        $trackId = data_get($request->callbackPayload, 'trackId')
            ?? $request->payment->gateway_reference;

        $payload = [
            'merchant' => $this->merchant(),
            'trackId' => $trackId,
        ];

        try {
            if (!$trackId) {
                return new VerifyPaymentResultDTO(
                    successful: false,
                    gatewayTransactionId: null,
                    gatewayStatus: 'missing_track_id',
                    gatewayMessage: 'Zibal trackId is missing.',
                    rawResponse: [
                        'request' => $payload,
                        'callback_payload' => $request->callbackPayload,
                    ],
                );
            }

            $response = Http::timeout($this->timeout())
                ->acceptJson()
                ->asJson()
                ->post($this->verifyUrl(), $payload);

            $data = $response->json() ?? [];

            $result = (int)data_get($data, 'result');
            $status = data_get($data, 'status');

            /**
             * Zibal verify success usually returns result = 100.
             * Some SDK examples also show status = 1 for successful paid payment.
             */
            $successful = $response->successful()
                && $result === 100
                && ((int)$status === 1 || $status === null);

            if (!$successful) {
                return new VerifyPaymentResultDTO(
                    successful: false,
                    gatewayTransactionId: data_get($data, 'refNumber')
                    ?? data_get($data, 'transactionId')
                    ?? null,
                    gatewayStatus: (string)($status ?? $result),
                    gatewayMessage: data_get($data, 'message')
                    ?? data_get($data, 'statusMessage')
                    ?? 'Zibal payment verification failed.',
                    rawResponse: [
                        'request' => $payload,
                        'response' => $data,
                        'callback_payload' => $request->callbackPayload,
                        'http_status' => $response->status(),
                    ],
                );
            }

            return new VerifyPaymentResultDTO(
                successful: true,
                gatewayTransactionId: (string)(
                    data_get($data, 'refNumber')
                    ?? data_get($data, 'transactionId')
                    ?? $trackId
                ),
                gatewayStatus: (string)($status ?? $result),
                gatewayMessage: data_get($data, 'message')
                ?? data_get($data, 'statusMessage')
                ?? 'Zibal payment verified successfully.',
                rawResponse: [
                    'request' => $payload,
                    'response' => $data,
                    'callback_payload' => $request->callbackPayload,
                    'http_status' => $response->status(),
                ],
            );
        } catch (Throwable $exception) {
            report($exception);

            return new VerifyPaymentResultDTO(
                successful: false,
                gatewayTransactionId: null,
                gatewayStatus: 'exception',
                gatewayMessage: 'Zibal payment verification exception.',
                rawResponse: [
                    'request' => $payload,
                    'callback_payload' => $request->callbackPayload,
                    'exception' => $exception->getMessage(),
                ],
            );
        }
    }

    private function merchant(): string
    {
        return (string)config('financial.gateways.zibal.merchant');
    }

    private function requestUrl(): string
    {
        return (string)config('financial.gateways.zibal.request_url');
    }

    private function verifyUrl(): string
    {
        return (string)config('financial.gateways.zibal.verify_url');
    }

    private function startUrl(): string
    {
        return rtrim((string)config('financial.gateways.zibal.start_url'), '/') . '/';
    }

    private function timeout(): int
    {
        return (int)config('financial.gateways.zibal.timeout', 20);
    }

    private function description(StartPaymentRequestDTO $request): string
    {
        return data_get($request->metadata, 'description')
            ?? data_get($request->metadata, 'order_number')
            ?? 'پرداخت سفارش';
    }

    /**
     * Convert toman to rial for Zibal.
     */
    private function toRial(int $amountInToman): int
    {
        return $amountInToman * 10;
    }
}
