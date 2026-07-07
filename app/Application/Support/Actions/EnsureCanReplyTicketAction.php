<?php

namespace App\Application\Support\Actions;

use App\Application\Support\Exceptions\TicketLimitExceededException;
use App\Domain\Identity\Models\User;
use App\Domain\Support\Enums\TicketMessageSenderTypeEnum;
use App\Domain\Support\Models\Ticket;
use Illuminate\Support\Facades\RateLimiter;

class EnsureCanReplyTicketAction
{
    private const REPLY_COOLDOWN_SECONDS = 15;

    private const REPLIES_WINDOW_MINUTES = 10;

    private const REPLIES_WINDOW_LIMIT = 15;

    public function execute(Ticket $ticket, User $user, string $body): void
    {
        $ticket->refresh();

        if ($ticket->user_id !== $user->id) {
            throw new TicketLimitExceededException(
                'شما اجازه ارسال پیام برای این تیکت را ندارید.'
            );
        }

        if (!$ticket->canReceiveReply()) {
            throw new TicketLimitExceededException(
                'امکان ارسال پیام برای تیکت بسته‌شده وجود ندارد.'
            );
        }

        $this->ensureMessageIsNotDuplicate($ticket, $user, $body);

        $this->ensureTenMinuteLimitNotExceeded($ticket);

        $this->ensureCooldownNotExceeded($ticket, $user);
    }

    private function ensureMessageIsNotDuplicate(Ticket $ticket, User $user, string $body): void
    {
        $latestMessage = $ticket->visibleMessages()
            ->latest('created_at')
            ->latest('id')
            ->first();

        if (!$latestMessage) {
            return;
        }

        $senderType = $latestMessage->sender_type;

        if ($senderType !== TicketMessageSenderTypeEnum::USER) {
            return;
        }

        if ((int)$latestMessage->user_id !== (int)$user->id) {
            return;
        }

        if ($this->normalizeMessage($latestMessage->body) === $this->normalizeMessage($body)) {
            throw new TicketLimitExceededException(
                'امکان ارسال پیام تکراری پشت سر هم وجود ندارد.'
            );
        }
    }

    private function ensureTenMinuteLimitNotExceeded(Ticket $ticket): void
    {
        $recentRepliesCount = $ticket->messages()
            ->where('sender_type', TicketMessageSenderTypeEnum::USER->value)
            ->where('is_internal_note', false)
            ->where('created_at', '>=', now()->subMinutes(self::REPLIES_WINDOW_MINUTES))
            ->count();

        if ($recentRepliesCount >= self::REPLIES_WINDOW_LIMIT) {
            throw new TicketLimitExceededException(
                'تعداد پاسخ‌های این تیکت در چند دقیقه اخیر زیاد بوده است. لطفاً کمی بعد دوباره پیام ارسال کنید.'
            );
        }
    }

    private function ensureCooldownNotExceeded(Ticket $ticket, User $user): void
    {
        $key = $this->replyCooldownKey($ticket, $user);

        $allowed = RateLimiter::attempt(
            key: $key,
            maxAttempts: 1,
            callback: fn() => true,
            decaySeconds: self::REPLY_COOLDOWN_SECONDS,
        );

        if (!$allowed) {
            $seconds = RateLimiter::availableIn($key);

            throw new TicketLimitExceededException(
                'برای ارسال پیام بعدی باید ' . $this->secondsLabel($seconds) . ' دیگر صبر کنید.'
            );
        }
    }

    private function replyCooldownKey(Ticket $ticket, User $user): string
    {
        return 'support:reply-ticket:ticket:' . $ticket->id . ':user:' . $user->id;
    }

    private function normalizeMessage(string $message): string
    {
        return mb_strtolower(
            preg_replace('/\s+/u', ' ', trim($message))
        );
    }

    private function secondsLabel(int $seconds): string
    {
        if ($seconds <= 0) {
            return 'چند لحظه';
        }

        return $seconds . ' ثانیه';
    }
}
