<?php

namespace App\Rules;

use App\Domain\Discussion\Models\Comment;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CommentSubmissionCooldown implements ValidationRule
{
    public function __construct(
        protected string  $commentableType,
        protected int     $commentableId,
        protected ?int    $userId,
        protected ?string $authorEmail,
        protected int     $seconds = 60,
    )
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = Comment::query()
            ->where('commentable_type', $this->commentableType)
            ->where('commentable_id', $this->commentableId)
            ->where('created_at', '>=', now()->subSeconds($this->seconds));

        if ($this->userId !== null) {
            $query->where('user_id', $this->userId);
        } else {
            $query->whereNull('user_id')
                ->where('author_email', $this->authorEmail);
        }

        if ($query->exists()) {
            $fail("لطفا {$this->seconds} ثانیه صبر کنید و دوباره تلاش کنید.");
        }
    }
}
