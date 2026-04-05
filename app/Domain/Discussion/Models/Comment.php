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

    /**
     * Get the model that owns the comment.
     *
     * @return MorphTo
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the parent comment.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get direct replies of this comment.
     *
     * @return HasMany
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->latest('id');
    }

    /**
     * Get the author user if the comment belongs to an authenticated user.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all reactions of the comment.
     *
     * @return HasMany
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    /**
     * Scope only approved comments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', CommentStatusEnum::APPROVED);
    }

    /**
     * Scope only pending comments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CommentStatusEnum::PENDING);
    }

    /**
     * Scope only rejected comments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', CommentStatusEnum::REJECTED);
    }

    /**
     * Scope only root comments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope comments for the given commentable model.
     *
     * @param Builder $query
     * @param Model $commentable
     * @return Builder
     */
    public function scopeForCommentable(Builder $query, Model $commentable): Builder
    {
        return $query
            ->where('commentable_type', $commentable->getMorphClass())
            ->where('commentable_id', $commentable->getKey());
    }

    /**
     * Sort comments by newest first.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    /**
     * Sort comments by oldest first.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOldestFirst(Builder $query): Builder
    {
        return $query->orderBy('created_at')->orderBy('id');
    }

    /**
     * Determine whether the comment is a root comment.
     *
     * @return bool
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Determine whether the comment is a reply.
     *
     * @return bool
     */
    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * Determine whether the comment is approved.
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->status === CommentStatusEnum::APPROVED;
    }

    /**
     * Determine whether the comment is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === CommentStatusEnum::PENDING;
    }

    /**
     * Determine whether the comment has been soft deleted.
     *
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->trashed();
    }

    /**
     * Determine whether the comment should be displayed as deleted.
     *
     * @return bool
     */
    public function shouldDisplayAsDeleted(): bool
    {
        return $this->trashed();
    }

    public function directReplies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
