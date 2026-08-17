<?php

namespace App\Console\Commands;

use App\Application\Raffle\Actions\Campaign\TransitionDrawCampaignStatusAction;
use App\Application\Raffle\DTOs\TransitionDrawCampaignStatusDTO;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use Illuminate\Console\Command;
use Throwable;

class SyncDrawCampaignStatusesCommand extends Command
{
    protected $signature = 'raffles:sync-statuses';
    protected $description = 'Open and close scheduled draw campaigns based on configured dates.';

    public function handle(TransitionDrawCampaignStatusAction $transition): int
    {
        $now = now()->toImmutable();

        DrawCampaign::query()
            ->where('status', DrawCampaignStatusEnum::SCHEDULED)
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', $now)
            ->orderBy('id')
            ->chunkById(100, function ($campaigns) use ($transition, $now): void {
                foreach ($campaigns as $campaign) {
                    try {
                        $openAt = $campaign->starts_at?->toImmutable() ?? $now;
                        $transition->execute(new TransitionDrawCampaignStatusDTO($campaign, DrawCampaignStatusEnum::OPEN, occurredAt: $openAt, reason: 'scheduled_open'));
                        $campaign->refresh();
                        if ($campaign->closes_at && $campaign->closes_at->lte($now)) {
                            $transition->execute(new TransitionDrawCampaignStatusDTO($campaign, DrawCampaignStatusEnum::CLOSED, occurredAt: $campaign->closes_at->toImmutable(), reason: 'scheduled_close'));
                        }
                    } catch (Throwable $exception) {
                        report($exception);
                    }
                }
            });

        DrawCampaign::query()
            ->where('status', DrawCampaignStatusEnum::OPEN)
            ->whereNotNull('closes_at')
            ->where('closes_at', '<=', $now)
            ->orderBy('id')
            ->chunkById(100, function ($campaigns) use ($transition): void {
                foreach ($campaigns as $campaign) {
                    try {
                        $transition->execute(new TransitionDrawCampaignStatusDTO($campaign, DrawCampaignStatusEnum::CLOSED, occurredAt: $campaign->closes_at->toImmutable(), reason: 'scheduled_close'));
                    } catch (Throwable $exception) {
                        report($exception);
                    }
                }
            });

        return self::SUCCESS;
    }
}
