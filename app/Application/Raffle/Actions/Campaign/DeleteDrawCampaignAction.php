<?php

namespace App\Application\Raffle\Actions\Campaign;

use App\Domain\Raffle\Models\DrawCampaign;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteDrawCampaignAction
{
    /** @throws Throwable */
    public function execute(DrawCampaign $campaign): void
    {
        DB::transaction(function () use ($campaign): void {
            DrawCampaign::query()->whereKey($campaign->getKey())->lockForUpdate()->firstOrFail()->delete();
        });
    }
}
