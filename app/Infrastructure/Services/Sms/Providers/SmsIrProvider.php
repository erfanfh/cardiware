<?php

namespace App\Infrastructure\Services\Sms\Providers;

use App\Infrastructure\Services\Sms\SmsProviderInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SmsIrProvider implements SmsProviderInterface
{
    private const BASE_URL = 'https://api.sms.ir/v1';

    public function sendOtp(string $mobile, string $code): string
    {
        $templateId = get_setting('general', 'smsir_otp_template_id');

        $parameterName = get_setting('general', 'smsir_otp_parameter_name') ?: 'Code';

        return $this->sendPattern($mobile, $templateId, [
            $parameterName => $code,
        ]);
    }

    public function sendPattern(string $mobile, string $patternId, array $values): string
    {
        if (blank($patternId)) {
            throw new RuntimeException('sms.ir template id is not configured.');
        }

        $apiKey = get_setting('general', 'smsir_api_key');

        if (blank($apiKey)) {
            throw new RuntimeException('sms.ir API key is not configured.');
        }

        $payload = [
            'mobile' => $this->normalizeMobile($mobile),
            'templateId' => (int)$patternId,
            'parameters' => $this->buildParameters($values),
        ];

        $response = Http::timeout(10)
            ->retry(2, 300)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'text/plain',
            ])
            ->asJson()
            ->post(self::BASE_URL . '/send/verify', $payload);

        return $this->handleResponse($response);
    }

    private function normalizeMobile(string $mobile): string
    {
        $mobile = preg_replace('/\D+/', '', $mobile);

        if (str_starts_with($mobile, '0098')) {
            $mobile = substr($mobile, 4);
        }

        if (str_starts_with($mobile, '98')) {
            $mobile = substr($mobile, 2);
        }

        if (str_starts_with($mobile, '0')) {
            $mobile = substr($mobile, 1);
        }

        if (!preg_match('/^9\d{9}$/', $mobile)) {
            throw new RuntimeException("Invalid mobile number for sms.ir: {$mobile}");
        }

        return $mobile;
    }

    private function buildParameters(array $values): array
    {
        if (empty($values)) {
            throw new RuntimeException('sms.ir template parameters cannot be empty.');
        }

        return collect($values)
            ->map(function (mixed $value, mixed $key) {
                if (is_int($key)) {
                    throw new RuntimeException(
                        'sms.ir template parameters must be associative. Example: ["Code" => "12345"].'
                    );
                }

                $name = $this->sanitizeParameterName((string)$key);

                if (blank($name)) {
                    throw new RuntimeException('sms.ir template parameter name cannot be empty.');
                }

                return [
                    'name' => $name,
                    'value' => $this->sanitizeParameterValue($value),
                ];
            })
            ->values()
            ->all();
    }

    private function sanitizeParameterName(string $name): string
    {
        $name = trim($name);

        // If someone passes "#Code#", convert it to "Code"
        return trim($name, " #\t\n\r\0\x0B");
    }

    private function sanitizeParameterValue(mixed $value): string
    {
        return trim(str_replace(["\n", "\r"], [' ', ' '], (string)$value));
    }

    private function handleResponse(Response $response): string
    {
        if (!$response->successful()) {
            throw new RuntimeException('sms.ir HTTP error: ' . $response->status());
        }

        $json = $response->json();

        if (!is_array($json)) {
            throw new RuntimeException('sms.ir invalid response: ' . $response->body());
        }

        $status = (int)($json['status'] ?? 0);

        if ($status !== 1) {
            $message = $json['message'] ?? 'Unknown error';

            throw new RuntimeException("sms.ir send failed. Status: {$status}, Message: {$message}");
        }

        $messageId = data_get($json, 'data.messageId');

        if (blank($messageId)) {
            throw new RuntimeException('sms.ir response does not contain messageId.');
        }

        return (string)$messageId;
    }
}
