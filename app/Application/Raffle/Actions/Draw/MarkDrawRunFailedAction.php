<?php

namespace App\Application\Raffle\Actions\Draw;

use App\Domain\Raffle\Enums\DrawRunStatusEnum;
use App\Domain\Raffle\Events\DrawFailedEvent;
use App\Domain\Raffle\Models\DrawRun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class MarkDrawRunFailedAction
{
    /** @throws Throwable */
    public function execute(int $drawRunId, string $reason): void
    {
        DB::transaction(function () use ($drawRunId, $reason): void {
            $run = DrawRun::query()->whereKey($drawRunId)->lockForUpdate()->first();

            if (!$run || $run->status === DrawRunStatusEnum::COMPLETED) {
                return;
            }

            $now = now()->toImmutable();
            $reason = Str::limit(trim($reason) ?: 'Unknown draw execution failure.', 4000, '');
            $metadata = $run->metadata ?? [];
            $attempts = is_array($metadata['attempts'] ?? null) ? $metadata['attempts'] : [];
            $attempts[] = [
                'status' => 'failed',
                'failed_at' => $now->toIso8601String(),
                'reason' => $reason,
            ];
            $metadata['attempts'] = $attempts;

            $run->forceFill([
                'status' => DrawRunStatusEnum::FAILED,
                'failed_at' => $now,
                'failure_reason' => $reason,
                'metadata' => $metadata,
            ])->save();

            DB::afterCommit(fn() => event(new DrawFailedEvent(
                campaignId: (int)$run->campaign_id,
                drawRunId: (int)$run->getKey(),
                reason: $reason,
                occurredAt: $now,
            )));
        }, 3);
    }
}
