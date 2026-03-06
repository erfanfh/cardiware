<?php

namespace App\Domain\Discussion\Models;

use App\Domain\Discussion\Enums\CommentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'commentable_id',
        'commentable_type',
        'parent_id',
        'user_id',
        'author_name',
        'author_email',
        'ip_address',
        'user_agent',
        'content',
        'status',
        'approved_at',
        'rating_percent',
        'likes_count',
        'dislikes_count',
        'score',
    ];

    protected $casts = [
        'status' => CommentStatusEnum::class,
    ];
}
