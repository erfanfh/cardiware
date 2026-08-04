<?php

namespace App\Domain\Discussion\Contracts;

use App\Domain\Discussion\Enums\CommentAuthorBadge;

interface HasDiscussionOwner
{
    /**
     * شناسه کاربری صاحب یا نویسنده محتوا.
     */
    public function discussionOwnerUserId(): ?int;

    /**
     * لیبلی که برای صاحب محتوا نمایش داده می‌شود.
     */
    public function discussionOwnerBadge(): CommentAuthorBadge;
}
