<?php

namespace App\Application\Shared\Jobs;

use App\Infrastructure\Services\Email\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Mailable;

final class SendEmailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public readonly string $recipient,
        public readonly Mailable $message,
    ) {}

    public function handle(EmailService $email): void
    {
        $email->send($this->recipient, $this->message);
    }

    public function backoff(): array
    {
        return [15, 60, 180];
    }
}
