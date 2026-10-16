<?php

namespace App\Infrastructure\Services\Email;

use App\Application\Shared\Jobs\SendEmailJob;
use Illuminate\Contracts\Mail\Factory;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\SentMessage;
use InvalidArgumentException;

class EmailService
{
    public function __construct(private readonly Factory $mail) {}

    public function send(string $recipient, Mailable $message): ?SentMessage
    {
        $this->validateRecipient($recipient);

        // The job owns queueing and retries, even for mailables implementing ShouldQueue.
        return $this->mail->mailer()->to($recipient)->sendNow(clone $message);
    }

    public function queue(string $recipient, Mailable $message): void
    {
        $this->validateRecipient($recipient);

        SendEmailJob::dispatch($recipient, clone $message)->afterCommit();
    }

    private function validateRecipient(string $recipient): void
    {
        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('A valid email recipient is required.');
        }
    }
}
