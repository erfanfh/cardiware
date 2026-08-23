<?php

namespace App\Application\Poll\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Poll\Models\Poll;
use Illuminate\Support\Facades\DB;

class CreatePollAction
{
    public function __construct(private readonly SyncPollOptionsAction $syncOptions) {}

    /** @param array<string,mixed> $data */
    public function execute(array $data, ?User $actor): Poll
    {
        return DB::transaction(function () use ($data, $actor): Poll {
            $options = $data['options'];
            unset($data['options']);
            $data['created_by_user_id'] = $actor?->getKey();
            $poll = Poll::query()->create($data);
            $this->syncOptions->execute($poll, $options);

            return $poll->load('options');
        });
    }
}
