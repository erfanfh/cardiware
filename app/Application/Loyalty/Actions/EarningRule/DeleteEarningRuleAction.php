<?php

namespace App\Application\Loyalty\Actions\EarningRule;

use App\Domain\Loyalty\Models\EarningRule;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteEarningRuleAction
{
    /**
     * @throws Throwable
     */
    public function execute(EarningRule $earningRule): void
    {
        DB::transaction(function () use ($earningRule): void {
            EarningRule::query()
                ->whereKey($earningRule->getKey())
                ->lockForUpdate()
                ->firstOrFail()
                ->delete();
        });
    }
}
