<?php

namespace App\Domain\Discussion\Models;

use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'commentable_id',
        'commentable_type',
        'root_id',
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
        'approved_at' => 'datetime',
        'rating_percent' => 'integer',
        'likes_count' => 'integer',
        'dislikes_count' => 'integer',
        'score' => 'integer',
    ];

    public function getDisplayNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->display_name
                ?? ($this->user->first_name . ' ' . $this->user->last_name)
                ?? 'کاربر';
        }

        return $this->author_name ?: 'کاربر';
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function root(): BelongsTo
    {
        return $this->belongsTo(self::class, 'root_id');
    }

    public function threadComments(): HasMany
    {
        return $this->hasMany(self::class, 'root_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldestFirst();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', CommentStatusEnum::APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CommentStatusEnum::PENDING);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', CommentStatusEnum::REJECTED);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeRepliesOnly(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeForCommentable(Builder $query, Model $commentable): Builder
    {
        return $query
            ->where('commentable_type', $commentable->getMorphClass())
            ->where('commentable_id', $commentable->getKey());
    }

    public function scopeForRootIds(Builder $query, array $rootIds): Builder
    {
        return $query->whereIn('root_id', $rootIds);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    public function scopeOldestFirst(Builder $query): Builder
    {
        return $query->orderBy('created_at')->orderBy('id');
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    public function isApproved(): bool
    {
        return $this->status === CommentStatusEnum::APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === CommentStatusEnum::PENDING;
    }

    public function isDeleted(): bool
    {
        return $this->trashed();
    }

    public function shouldDisplayAsDeleted(): bool
    {
        return $this->trashed();
    }
}
