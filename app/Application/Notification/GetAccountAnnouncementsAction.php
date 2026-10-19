<?php

namespace App\Application\Notification;

use App\Domain\Notification\Models\AccountAnnouncement;
use Illuminate\Database\Eloquent\Collection;

class GetAccountAnnouncementsAction
{
    /** @return Collection<int, AccountAnnouncement> */
    public function execute(): Collection
    {
        return AccountAnnouncement::query()->visible()->latest('id')->get();
    }
}
