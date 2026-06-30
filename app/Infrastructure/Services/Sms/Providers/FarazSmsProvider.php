<?php

namespace App\Infrastructure\Services\Sms\Providers;

use App\Infrastructure\Services\Sms\SmsProviderInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FarazSmsProvider implements SmsProviderInterface
{
    private const BASE_URL = 'https://api.iranpayamak.com';

    public function sendOtp(string $mobile, string $code): string
    {
        $patternCode = get_setting('general', 'farazsms_otp_pattern');

        $attributeName = get_setting('general', 'farazsms_otp_attribute_name') ?: 'code';

        return $this->sendPattern($mobile, $patternCode, [
            $attributeName => $code,
        ]);
    }

    public function sendPattern(string $mobile, string $patternId, array $values): string
    {
        if (blank($patternId)) {
            throw new RuntimeException('FarazSMS pattern code is not configured.');
        }

        $apiKey = get_setting('general', 'farazsms_api_key');

        if (blank($apiKey)) {
            throw new RuntimeException('FarazSMS API key is not configured.');
        }

        $lineNumber = get_setting('general', 'farazsms_line_number');

        if (blank($lineNumber)) {
            throw new RuntimeException('FarazSMS line number is not configured.');
        }

        $numberFormat = 'english';

        if (!in_array($numberFormat, ['english', 'persian'], true)) {
            throw new RuntimeException('FarazSMS number format must be either english or persian.');
        }

        $payload = [
            'code' => (string)$patternId,
            'attributes' => $this->buildAttributes($values),
            'recipient' => $this->normalizeMobile($mobile),
            'line_number' => (string)$lineNumber,
            'number_format' => $numberFormat,
        ];

        $response = Http::timeout(10)
            ->retry(2, 300)
            ->withHeaders([
                'Api-Key' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->asJson()
            ->post(self::BASE_URL . '/ws/v1/sms/pattern', $payload);

        return $this->handleResponse($response);
    }

    private function normalizeMobile(string $mobile): string
    {
        $mobile = preg_replace('/\D+/', '', $mobile);

        if (str_starts_with($mobile, '0098')) {
            $mobile = '0' . substr($mobile, 4);
        }

        if (str_starts_with($mobile, '98')) {
            $mobile = '0' . substr($mobile, 2);
        }

        if (preg_match('/^9\d{9}$/', $mobile)) {
            $mobile = '0' . $mobile;
        }

        if (!preg_match('/^09\d{9}$/', $mobile)) {
            throw new RuntimeException("Invalid mobile number for FarazSMS: {$mobile}");
        }

        return $mobile;
    }

    private function buildAttributes(array $values): array
    {
        if (empty($values)) {
            return [];
        }

        return collect($values)
            ->mapWithKeys(function (mixed $value, mixed $key) {
                $attributeName = is_int($key)
                    ? 'var' . ($key + 1)
                    : $this->sanitizeAttributeName((string)$key);

                if (blank($attributeName)) {
                    throw new RuntimeException('FarazSMS attribute name cannot be empty.');
                }

                return [
                    $attributeName => $this->sanitizeAttributeValue($value),
                ];
            })
            ->all();
    }

    private function sanitizeAttributeName(string $name): string
    {
        $name = trim($name);

        return trim($name, " #%\t\n\r\0\x0B");
    }

    private function sanitizeAttributeValue(mixed $value): string
    {
        return trim(str_replace(["\n", "\r"], [' ', ' '], (string)$value));
    }

    private function handleResponse(Response $response): string
    {
        if (!$response->successful()) {
            throw new RuntimeException(
                'FarazSMS HTTP error: ' . $response->status() . ' - ' . $response->body()
            );
        }

        $json = $response->json();

        if (!is_array($json)) {
            throw new RuntimeException('FarazSMS invalid response: ' . $response->body());
        }

        $trackingId = data_get($json, 'data')
            ?? data_get($json, 'data.id')
            ?? data_get($json, 'data.bulk_id')
            ?? data_get($json, 'send_request_id');

        if (filled($trackingId)) {
            return (string)$trackingId;
        }

        $status = data_get($json, 'status');
        $message = data_get($json, 'message') ?? data_get($json, 'messages');

        throw new RuntimeException(
            'FarazSMS send failed. Status: '
            . json_encode($status, JSON_UNESCAPED_UNICODE)
            . ', Message: '
            . json_encode($message, JSON_UNESCAPED_UNICODE)
        );
    }
}
