<?php

namespace App\Application\Poll\Actions;

use App\Domain\Poll\Models\Poll;
use Illuminate\Support\Facades\DB;

class UpdatePollAction
{
    public function __construct(private readonly SyncPollOptionsAction $syncOptions) {}

    /** @param array<string,mixed> $data */
    public function execute(Poll $poll, array $data): Poll
    {
        return DB::transaction(function () use ($poll, $data): Poll {
            $options = $data['options'];
            unset($data['options']);
            $poll->update($data);
            $this->syncOptions->execute($poll, $options);

            return $poll->refresh()->load('options');
        });
    }
}
