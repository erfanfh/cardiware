<?php

namespace App\Application\Support\Actions;

use App\Application\Support\Exceptions\TicketLimitExceededException;
use App\Domain\Identity\Models\User;
use App\Domain\Support\Enums\TicketStatusEnum;
use App\Domain\Support\Models\Ticket;
use Illuminate\Support\Facades\RateLimiter;

class EnsureCanCreateTicketAction
{
    private const CREATE_COOLDOWN_SECONDS = 120;

    private const DAILY_LIMIT = 10;

    private const OPEN_TICKETS_LIMIT = 5;

    public function execute(User $user): void
    {
        $this->ensureOpenTicketsLimitNotExceeded($user);

        $this->ensureDailyLimitNotExceeded($user);

        $this->ensureCooldownNotExceeded($user);
    }

    private function ensureOpenTicketsLimitNotExceeded(User $user): void
    {
        $openTicketsCount = Ticket::query()
            ->where('user_id', $user->id)
            ->where('status', '!=', TicketStatusEnum::CLOSED->value)
            ->count();

        if ($openTicketsCount >= self::OPEN_TICKETS_LIMIT) {
            throw new TicketLimitExceededException(
                'شما در حال حاضر ۵ تیکت باز دارید. لطفاً ابتدا منتظر پاسخ پشتیبانی بمانید یا تیکت‌های قبلی را پیگیری کنید.'
            );
        }
    }

    private function ensureDailyLimitNotExceeded(User $user): void
    {
        $todayTicketsCount = Ticket::query()
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [
                now()->startOfDay(),
                now()->endOfDay(),
            ])
            ->count();

        if ($todayTicketsCount >= self::DAILY_LIMIT) {
            throw new TicketLimitExceededException(
                'شما امروز به سقف مجاز ثبت تیکت رسیده‌اید. فردا دوباره می‌توانید تیکت جدید ثبت کنید.'
            );
        }
    }

    private function ensureCooldownNotExceeded(User $user): void
    {
        $key = $this->createCooldownKey($user);

        $allowed = RateLimiter::attempt(
            key: $key,
            maxAttempts: 1,
            callback: fn() => true,
            decaySeconds: self::CREATE_COOLDOWN_SECONDS,
        );

        if (!$allowed) {
            $seconds = RateLimiter::availableIn($key);

            throw new TicketLimitExceededException(
                'برای ثبت تیکت جدید باید ' . $this->secondsLabel($seconds) . ' دیگر صبر کنید.'
            );
        }
    }

    private function createCooldownKey(User $user): string
    {
        return 'support:create-ticket:user:' . $user->id;
    }

    private function secondsLabel(int $seconds): string
    {
        if ($seconds <= 0) {
            return 'چند لحظه';
        }

        if ($seconds < 60) {
            return $seconds . ' ثانیه';
        }

        $minutes = (int)ceil($seconds / 60);

        return $minutes . ' دقیقه';
    }
}
