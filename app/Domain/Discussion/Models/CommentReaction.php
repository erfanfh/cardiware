<?php

namespace App\Domain\Discussion\Models;

use App\Domain\Discussion\Enums\CommentReactionTypeEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentReaction extends Model
{
    protected $fillable = [
        'comment_id',
        'user_id',
        'ip_hash',
        'type',
    ];

    protected $casts = [
        'type' => CommentReactionTypeEnum::class,
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
