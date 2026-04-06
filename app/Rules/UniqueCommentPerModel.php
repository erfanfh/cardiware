<?php

namespace App\Rules;

use App\Domain\Discussion\Models\Comment;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueCommentPerModel implements ValidationRule
{
    public function __construct(
        protected string  $commentableType,
        protected int     $commentableId,
        protected ?int    $userId,
        protected ?string $authorEmail,
        protected ?int    $parentId = null,
    )
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || trim($value) === '') {
            return;
        }

        $normalizedValue = $this->normalize($value);

        $query = Comment::query()
            ->where('commentable_type', $this->commentableType)
            ->where('commentable_id', $this->commentableId);

        if ($this->parentId !== null) {
            $query->where('parent_id', $this->parentId);
        } else {
            $query->whereNull('parent_id');
        }

        if ($this->userId !== null) {
            $query->where('user_id', $this->userId);
        } else {
            $query->whereNull('user_id')
                ->where('author_email', $this->authorEmail);
        }

        $exists = $query
            ->select(['id', 'content'])
            ->get()
            ->contains(function (Comment $comment) use ($normalizedValue) {
                return $this->normalize((string)$comment->content) === $normalizedValue;
            });

        if ($exists) {
            $fail('شما قبلا همین دیدگاه را ثبت کرده‌اید.');
        }
    }

    protected function normalize(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return mb_strtolower($text);
    }
}
