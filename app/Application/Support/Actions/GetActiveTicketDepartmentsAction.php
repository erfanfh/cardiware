<?php

namespace App\Application\Support\Actions;

use App\Application\Shared\Cache\PublicReadCache;
use App\Domain\Support\Models\TicketDepartment;
use Illuminate\Database\Eloquent\Collection;

class GetActiveTicketDepartmentsAction
{
    public function execute(): Collection
    {
        return app(PublicReadCache::class)->remember(
            'ticket-departments', [], [TicketDepartment::class],
            fn () => TicketDepartment::query()
                ->active()
                ->ordered()
                ->get(),
        );
    }
}
