<?php

namespace App\Application\Discussion\DTOs;

use Illuminate\Database\Eloquent\Model;

class CreateCommentDataDTO
{
    public function __construct(
        public Model   $commentable,
        public ?int    $parentId,
        public ?int    $userId,
        public ?string $authorName,
        public ?string $authorEmail,
        public string  $content,
        public ?int    $ratingPercent,
        public ?string $ipAddress,
        public ?string $userAgent,
        public bool    $allowReplyToUnapprovedParent = false,
        public bool    $bypassCooldown = false,
    )
    {
    }
}
