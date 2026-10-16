<?php

namespace App\Application\Identity\Jobs;

use App\Application\Identity\DTOs\LoginAlertData;
use App\Application\Identity\Support\LoginAlertSmsPayloadFactory;
use App\Infrastructure\Services\Sms\SmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SendLoginAlertSmsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public readonly string $mobile,
        public readonly LoginAlertData $login,
    ) {}

    public function handle(SmsService $sms, LoginAlertSmsPayloadFactory $factory): void
    {
        $payload = $factory->make($this->login);
        $sms->sendPattern($this->mobile, $payload['pattern_id'], $payload['values']);
    }

    public function backoff(): array
    {
        return [15, 60, 180];
    }
}
