<?php

namespace App\Application\Raffle\Jobs;

use App\Application\Raffle\Support\RaffleWinnerSmsPayloadFactory;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Models\DrawCampaignEvent;
use App\Domain\Raffle\Models\DrawWinner;
use App\Infrastructure\Services\Setting\Groups\RaffleSettings;
use App\Infrastructure\Services\Sms\SmsService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendRaffleWinnerSmsJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public int $uniqueFor = 600;

    public function __construct(
        public readonly int $winnerId,
        public readonly string $type = 'winner',
    ) {}

    public function uniqueId(): string
    {
        return "raffle-winner-sms:{$this->type}:{$this->winnerId}";
    }

    public function handle(
        SmsService $sms,
        RaffleWinnerSmsPayloadFactory $payloadFactory,
        RaffleSettings $settings,
    ): void {
        $enabled = $this->type === 'reminder'
            ? $settings->get('winner_reminder_sms_enabled', false)
            : $settings->get('winner_sms_enabled', false);

        if (! $enabled) {
            return;
        }

        $winner = DrawWinner::query()
            ->with(['user', 'campaign', 'prize'])
            ->find($this->winnerId);

        if (
            ! $winner
            || ! $winner->isEffectivePrimary()
            || $winner->status !== DrawWinnerStatusEnum::PENDING
            || blank($winner->user?->mobile)
        ) {
            return;
        }

        $sentAtColumn = $this->type === 'reminder'
            ? 'winner_reminder_sms_sent_at'
            : 'winner_sms_sent_at';
        $messageIdColumn = $this->type === 'reminder'
            ? 'winner_reminder_sms_message_id'
            : 'winner_sms_message_id';
        $failedAtColumn = $this->type === 'reminder'
            ? 'winner_reminder_sms_failed_at'
            : 'winner_sms_failed_at';
        $failureColumn = $this->type === 'reminder'
            ? 'winner_reminder_sms_failure'
            : 'winner_sms_failure';

        if ($winner->{$sentAtColumn}) {
            return;
        }

        $payload = $payloadFactory->make($winner, $this->type);
        $messageId = $sms->sendPattern(
            (string) $winner->user->mobile,
            $payload['pattern_id'],
            $payload['values'],
        );

        DrawWinner::query()
            ->whereKey($winner->getKey())
            ->whereNull($sentAtColumn)
            ->update([
                $sentAtColumn => now(),
                $messageIdColumn => $messageId,
                $failedAtColumn => null,
                $failureColumn => null,
                'updated_at' => now(),
            ]);

        DrawCampaignEvent::query()->create([
            'campaign_id' => $winner->campaign_id,
            'type' => $this->type === 'reminder' ? 'winner_reminder_sms_sent' : 'winner_sms_sent',
            'subject_type' => 'winner',
            'subject_id' => $winner->getKey(),
            'payload' => ['message_id' => $messageId, 'mobile' => $this->maskedMobile((string) $winner->user->mobile)],
            'occurred_at' => now(),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $winner = DrawWinner::query()->find($this->winnerId);
        if (! $winner) {
            return;
        }

        $failedAtColumn = $this->type === 'reminder'
            ? 'winner_reminder_sms_failed_at'
            : 'winner_sms_failed_at';
        $failureColumn = $this->type === 'reminder'
            ? 'winner_reminder_sms_failure'
            : 'winner_sms_failure';

        DrawWinner::query()->whereKey($this->winnerId)->update([
            $failedAtColumn => now(),
            $failureColumn => mb_substr((string) $exception?->getMessage(), 0, 2000),
            'updated_at' => now(),
        ]);

        DrawCampaignEvent::query()->create([
            'campaign_id' => $winner->campaign_id,
            'type' => $this->type === 'reminder' ? 'winner_reminder_sms_failed' : 'winner_sms_failed',
            'subject_type' => 'winner',
            'subject_id' => $winner->getKey(),
            'payload' => ['error' => mb_substr((string) $exception?->getMessage(), 0, 500)],
            'occurred_at' => now(),
        ]);
    }

    public function backoff(): array
    {
        return [15, 60, 180];
    }

    private function maskedMobile(string $mobile): string
    {
        return mb_strlen($mobile) > 7
            ? mb_substr($mobile, 0, 4).'***'.mb_substr($mobile, -4)
            : '***';
    }
}
