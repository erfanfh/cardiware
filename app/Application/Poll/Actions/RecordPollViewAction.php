<?php

namespace App\Application\Poll\Actions;

use App\Application\Poll\Support\PollVoterIdentity;
use App\Domain\Poll\Models\Poll;
use App\Domain\Poll\Models\PollView;
use Illuminate\Support\Facades\DB;

class RecordPollViewAction
{
    public function execute(Poll $poll, PollVoterIdentity $identity): void
    {
        DB::transaction(function () use ($poll, $identity): void {
            $inserted = PollView::query()->insertOrIgnore([
                'poll_id' => $poll->getKey(),
                'user_id' => $identity->userId,
                'visitor_hash' => hash('sha256', $identity->key),
                'ip_hash' => $identity->ipHash,
                'viewed_on' => now()->toDateString(),
                'viewed_at' => now(),
            ]);

            if ($inserted === 1) {
                Poll::query()->whereKey($poll->getKey())->increment('views_count');
                $poll->setAttribute('views_count', (int) $poll->views_count + 1);
            }
        });
    }
}
