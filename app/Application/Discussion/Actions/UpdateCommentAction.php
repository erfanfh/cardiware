<?php

namespace App\Application\Discussion\Actions;

use App\Domain\Discussion\Models\Comment;
use Illuminate\Validation\ValidationException;

class UpdateCommentAction
{
    public function execute(Comment $comment, string $content, ?int $ratingStars): Comment
    {
        if ($comment->isRoot() && $ratingStars === null) {
            throw ValidationException::withMessages([
                'rating_stars' => 'انتخاب امتیاز برای دیدگاه اصلی الزامی است.',
            ]);
        }

        if ($comment->isReply() && $ratingStars !== null) {
            throw ValidationException::withMessages([
                'rating_stars' => 'پاسخ‌ها امتیاز مستقل ندارند.',
            ]);
        }

        if ($ratingStars !== null && ($ratingStars < 1 || $ratingStars > 5)) {
            throw ValidationException::withMessages([
                'rating_stars' => 'امتیاز باید بین ۱ تا ۵ ستاره باشد.',
            ]);
        }

        $comment->forceFill([
            'content' => str_replace(["\r\n", "\r"], "\n", trim($content)),
            'rating_percent' => $comment->isRoot() ? (int)$ratingStars * 20 : null,
        ])->save();

        return $comment->refresh();
    }
}
