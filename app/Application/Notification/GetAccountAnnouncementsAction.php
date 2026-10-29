<?php

namespace App\Application\Notification;

use App\Application\Shared\Cache\PublicReadCache;
use App\Domain\Notification\Models\AccountAnnouncement;
use Illuminate\Database\Eloquent\Collection;

class GetAccountAnnouncementsAction
{
    /** @return Collection<int, AccountAnnouncement> */
    public function execute(): Collection
    {
        return app(PublicReadCache::class)->remember(
            'account-announcements', [], [AccountAnnouncement::class],
            fn () => AccountAnnouncement::query()->visible()->latest('id')->get(),
            [AccountAnnouncement::class => ['starts_at', 'ends_at']],
        );
    }
}
