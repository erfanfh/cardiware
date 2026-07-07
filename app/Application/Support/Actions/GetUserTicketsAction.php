<?php

namespace App\Application\Support\Actions;

use App\Domain\Support\Models\Ticket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetUserTicketsAction
{
    public function execute(int $userId, int $perPage = 6): LengthAwarePaginator
    {
        return Ticket::query()
            ->where('user_id', $userId)
            ->with([
                'department',
                'product',
                'latestVisibleMessage.user',
            ])
            ->withCount([
                'visibleMessages as visible_messages_count',
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
