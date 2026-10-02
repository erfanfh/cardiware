<?php

namespace App\Domain\Notification;

final class NotificationEvents
{
    public const COMMENT_CREATED = 'comment.created';
    public const COMMENT_REPLIED = 'comment.replied';
    public const COMMENT_APPROVED = 'comment.approved';
    public const WITHDRAWAL_RESOLVED = 'withdrawal.resolved';

    public const LABELS = [
        self::COMMENT_CREATED => 'ثبت دیدگاه برای نویسنده مطلب',
        self::COMMENT_REPLIED => 'ثبت پاسخ برای صاحب دیدگاه والد',
        self::COMMENT_APPROVED => 'تأیید دیدگاه برای نویسنده دیدگاه',
        self::WITHDRAWAL_RESOLVED => 'نتیجه نهایی درخواست برداشت وجه',
    ];
}
