<?php

namespace App\Console\Commands;

use App\Application\Raffle\Jobs\SendRaffleWinnerSmsJob;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Models\DrawWinner;
use App\Infrastructure\Services\Setting\Groups\RaffleSettings;
use Illuminate\Console\Command;

class SendRaffleWinnerRemindersCommand extends Command
{
    protected $signature = 'raffles:send-winner-reminders';

    protected $description = 'Queue SMS reminders for raffle winners nearing their confirmation deadline.';

    public function handle(RaffleSettings $settings): int
    {
        if (! $settings->get('winner_reminder_sms_enabled', false)) {
            return self::SUCCESS;
        }

        $until = now()->addHours(max(1, (int) $settings->get('winner_reminder_hours_before_deadline', 12)));

        DrawWinner::query()
            ->where('status', DrawWinnerStatusEnum::PENDING->value)
            ->whereNotNull('confirmation_deadline_at')
            ->where('confirmation_deadline_at', '>', now())
            ->where('confirmation_deadline_at', '<=', $until)
            ->whereNull('winner_reminder_sms_sent_at')
            ->where(function ($query): void {
                $query->where('kind', 'primary')->orWhereNotNull('promoted_at');
            })
            ->orderBy('id')
            ->chunkById(100, function ($winners): void {
                foreach ($winners as $winner) {
                    SendRaffleWinnerSmsJob::dispatch((int) $winner->getKey(), 'reminder');
                }
            });

        return self::SUCCESS;
    }
}
