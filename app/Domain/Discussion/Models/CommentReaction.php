<?php

namespace App\Domain\Discussion\Models;

use App\Domain\Discussion\Enums\CommentReactionTypeEnum;
use Illuminate\Database\Eloquent\Model;

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
}
