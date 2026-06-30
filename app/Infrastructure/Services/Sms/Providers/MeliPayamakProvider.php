<?php

namespace App\Infrastructure\Services\Sms\Providers;

use App\Infrastructure\Services\Sms\SmsProviderInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MeliPayamakProvider implements SmsProviderInterface
{
    public function sendOtp(string $mobile, string $code): string
    {
        $patternId = get_setting('general', 'melipayamak_otp_pattern');

        return $this->sendPattern($mobile, $patternId, [$code]);
    }

    public function sendPattern(string $mobile, string $patternId, array $values): string
    {
        if (blank($patternId)) {
            throw new RuntimeException('MeliPayamak pattern is not configured.');
        }

        $username = get_setting('general', 'melipayamak_username');
        $password = get_setting('general', 'melipayamak_password');

        if (blank($username) || blank($password)) {
            throw new RuntimeException('MeliPayamak credentials are not configured.');
        }

        $mobile = $this->normalizeMobile($mobile);

        $text = collect($values)
            ->map(fn($value) => $this->sanitizePatternValue($value))
            ->implode(';');

        $response = Http::timeout(10)
            ->retry(2, 300)
            ->get('https://api.payamak-panel.com/post/Send.asmx/SendByBaseNumber2', [
                'username' => $username,
                'password' => $password,
                'text' => $text,
                'to' => $mobile,
                'bodyId' => $patternId,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('MeliPayamak HTTP error: ' . $response->status());
        }

        $result = $this->extractResult($response->body());

        if (!$this->isSuccessfulResult($result)) {
            throw new RuntimeException("MeliPayamak send failed. Result: {$result}");
        }

        return $result;
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

        if (!preg_match('/^09\d{9}$/', $mobile)) {
            throw new RuntimeException("Invalid mobile number: {$mobile}");
        }

        return $mobile;
    }

    private function sanitizePatternValue(mixed $value): string
    {
        return trim(str_replace([';', "\n", "\r"], [' ', ' ', ' '], (string)$value));
    }

    private function extractResult(string $body): string
    {
        if (preg_match('/<string[^>]*>(.*?)<\/string>/s', $body, $matches)) {
            return html_entity_decode(trim($matches[1]));
        }

        return trim($body);
    }

    private function isSuccessfulResult(string $result): bool
    {
        return ctype_digit($result) && (int)$result > 0;
    }
}
