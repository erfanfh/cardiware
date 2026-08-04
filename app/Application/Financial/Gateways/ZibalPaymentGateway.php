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
        $storedTrackId = $request->payment->gateway_reference;

        $callbackTrackId = data_get($request->callbackPayload, 'trackId');

        $trackId = $callbackTrackId ?: $storedTrackId;

        $payload = [
            'merchant' => $this->merchant(),
            'trackId' => $trackId,
        ];

        if (blank($trackId)) {
            return new VerifyPaymentResultDTO(
                successful: false,
                gatewayTransactionId: null,
                gatewayStatus: 'missing_track_id',
                gatewayMessage: 'Zibal trackId is missing.',
                rawResponse: [
                    'request' => $payload,
                    'callback_payload' => $request->callbackPayload,
                ],
                terminal: false,
            );
        }

        /*
         * Prevent verifying one local Payment using another trackId.
         */
        if (
            filled($storedTrackId)
            && filled($callbackTrackId)
            && (string)$storedTrackId !== (string)$callbackTrackId
        ) {
            return new VerifyPaymentResultDTO(
                successful: false,
                gatewayTransactionId: null,
                gatewayStatus: 'track_id_mismatch',
                gatewayMessage: 'Callback trackId does not match the stored payment trackId.',
                rawResponse: [
                    'stored_track_id' => $storedTrackId,
                    'callback_track_id' => $callbackTrackId,
                    'callback_payload' => $request->callbackPayload,
                ],
                terminal: false,
            );
        }

        try {
            $response = Http::connectTimeout(5)
                ->timeout($this->timeout())
                ->retry(
                    times: 2,
                    sleepMilliseconds: 300,
                    throw: false,
                )
                ->acceptJson()
                ->asJson()
                ->post($this->verifyUrl(), $payload);

            $data = $response->json() ?? [];

            $result = (int)data_get($data, 'result');
            $status = data_get($data, 'status');

            $rawResponse = [
                'request' => $payload,
                'response' => $data,
                'callback_payload' => $request->callbackPayload,
                'http_status' => $response->status(),
            ];

            /*
             * Temporary HTTP or gateway communication problem.
             *
             * Never mark the Payment as FAILED because the bank payment
             * may already have been completed.
             */
            if (!$response->successful()) {
                return new VerifyPaymentResultDTO(
                    successful: false,
                    gatewayTransactionId: null,
                    gatewayStatus: 'temporary_http_error',
                    gatewayMessage: 'Zibal verification returned a temporary HTTP error.',
                    rawResponse: $rawResponse,
                    terminal: false,
                );
            }

            /*
             * Successful verification.
             *
             * Some versions of the response may not include status.
             */
            if ($result === 100 && ($status === null || (int)$status === 1)) {
                $responseAmount = data_get($data, 'amount');

                if (
                    $responseAmount !== null
                    && (int)$responseAmount !== $this->toRial($request->payment->amount)
                ) {
                    return new VerifyPaymentResultDTO(
                        successful: false,
                        gatewayTransactionId: null,
                        gatewayStatus: 'amount_mismatch',
                        gatewayMessage: 'Zibal verified amount does not match the local payment amount.',
                        rawResponse: $rawResponse,
                        terminal: false,
                    );
                }

                $responseOrderId = data_get($data, 'orderId');

                if (
                    filled($responseOrderId)
                    && (string)$responseOrderId !== (string)$request->payment->tracking_code
                ) {
                    return new VerifyPaymentResultDTO(
                        successful: false,
                        gatewayTransactionId: null,
                        gatewayStatus: 'order_id_mismatch',
                        gatewayMessage: 'Zibal orderId does not match the local payment tracking code.',
                        rawResponse: $rawResponse,
                        terminal: false,
                    );
                }

                return new VerifyPaymentResultDTO(
                    successful: true,
                    gatewayTransactionId: (string)(
                        data_get($data, 'refNumber')
                        ?? data_get($data, 'transactionId')
                        ?? $trackId
                    ),
                    gatewayStatus: '100',
                    gatewayMessage: data_get($data, 'message')
                    ?? data_get($data, 'statusMessage')
                    ?? 'Zibal payment verified successfully.',
                    rawResponse: $rawResponse,
                );
            }

            /*
             * The transaction has already been verified.
             *
             * This is an idempotent success, not a payment failure.
             * It commonly happens when browser and server callbacks
             * arrive concurrently.
             */
            if ($result === 201) {
                return new VerifyPaymentResultDTO(
                    successful: true,
                    gatewayTransactionId: (string)(
                        data_get($data, 'refNumber')
                        ?? data_get($data, 'transactionId')
                        ?? $request->payment->gateway_transaction_id
                        ?? $trackId
                    ),
                    gatewayStatus: '201',
                    gatewayMessage: data_get($data, 'message')
                    ?? data_get($data, 'statusMessage')
                    ?? 'Zibal payment was already verified.',
                    rawResponse: $rawResponse,
                );
            }

            /*
             * Zibal explicitly says the transaction was unpaid or failed.
             */
            if ($result === 202) {
                return new VerifyPaymentResultDTO(
                    successful: false,
                    gatewayTransactionId: null,
                    gatewayStatus: '202',
                    gatewayMessage: data_get($data, 'message')
                    ?? data_get($data, 'statusMessage')
                    ?? 'Zibal transaction was unpaid or unsuccessful.',
                    rawResponse: $rawResponse,
                    terminal: true,
                );
            }

            /*
             * Paid but not verified.
             */
            if ((int)$status === 2) {
                return new VerifyPaymentResultDTO(
                    successful: false,
                    gatewayTransactionId: null,
                    gatewayStatus: 'paid_unverified',
                    gatewayMessage: 'Zibal transaction is paid but has not been verified yet.',
                    rawResponse: $rawResponse,
                    terminal: false,
                );
            }

            /*
             * Unknown result codes must not convert a possibly successful
             * bank transaction into a terminal local failure.
             */
            return new VerifyPaymentResultDTO(
                successful: false,
                gatewayTransactionId: null,
                gatewayStatus: (string)($result ?: 'unknown'),
                gatewayMessage: data_get($data, 'message')
                ?? data_get($data, 'statusMessage')
                ?? 'Zibal verification result is currently indeterminate.',
                rawResponse: $rawResponse,
                terminal: false,
            );
        } catch (Throwable $exception) {
            report($exception);

            /*
             * A timeout or network exception says nothing definitive
             * about the actual bank transaction.
             */
            return new VerifyPaymentResultDTO(
                successful: false,
                gatewayTransactionId: null,
                gatewayStatus: 'verification_exception',
                gatewayMessage: 'Zibal verification could not be completed.',
                rawResponse: [
                    'request' => $payload,
                    'callback_payload' => $request->callbackPayload,
                    'exception' => $exception->getMessage(),
                ],
                terminal: false,
            );
        }
    }

    private function merchant(): string
    {
        if (get_setting('general', 'zibal_sandbox')) {
            return 'zibal';
        }

        return get_setting('general', 'zibal_merchant_id');
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
