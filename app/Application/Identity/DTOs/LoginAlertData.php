<?php

namespace App\Application\Identity\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

final readonly class LoginAlertData
{
    public function __construct(
        public string $name,
        public CarbonImmutable $occurredAt,
        public string $ip,
        public string $userAgent,
        public string $source,
    ) {}

    public static function fromRequest(string $name, Request $request, string $source): self
    {
        return new self(
            name: $name,
            occurredAt: CarbonImmutable::now(),
            ip: $request->ip() ?: 'نامشخص',
            userAgent: mb_substr(str_replace(["\r", "\n"], ' ', (string) $request->userAgent()), 0, 500),
            source: $source,
        );
    }

    public function time(): string
    {
        return jalali_date($this->occurredAt->setTimezone('Asia/Tehran'), 'Y/m/d H:i:s');
    }

    public function sourceLabel(): string
    {
        return $this->source === 'launcher' ? 'لانچر' : 'وب‌سایت';
    }
}
