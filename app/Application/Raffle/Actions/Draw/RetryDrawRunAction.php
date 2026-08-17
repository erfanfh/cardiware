<?php

namespace App\Application\Raffle\Actions\Draw;

use App\Application\Raffle\Exceptions\DrawRunExecutionException;
use App\Application\Raffle\Jobs\ExecuteDrawRunJob;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Enums\DrawRunStatusEnum;
use App\Domain\Raffle\Models\DrawRun;
use Illuminate\Support\Facades\DB;
use Throwable;

class RetryDrawRunAction
{
    /** @throws Throwable */
    public function execute(DrawRun $drawRun): DrawRun
    {
        return DB::transaction(function () use ($drawRun): DrawRun {
            $run = DrawRun::query()->whereKey($drawRun->getKey())->lockForUpdate()->firstOrFail();
            $run->loadMissing('campaign');

            if (!in_array($run->status, [DrawRunStatusEnum::FAILED, DrawRunStatusEnum::PENDING], true)) {
                throw new DrawRunExecutionException('Only a failed or pending draw run can be queued again.');
            }

            if ($run->campaign->status !== DrawCampaignStatusEnum::DRAWING) {
                throw new DrawRunExecutionException('Draw campaign is not in DRAWING status.');
            }

            if ($run->winners()->exists()) {
                throw new DrawRunExecutionException('Draw run with persisted winners cannot be retried.');
            }

            DB::afterCommit(fn() => ExecuteDrawRunJob::dispatch((int)$run->getKey()));

            return $run;
        }, 3);
    }
}
