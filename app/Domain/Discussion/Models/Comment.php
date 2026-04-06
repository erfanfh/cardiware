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

    /**
     * @return MorphTo
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Comment, self>
     */
    public function root(): BelongsTo
    {
        return $this->belongsTo(self::class, 'root_id');
    }

    /**
     * @return HasMany<Comment, self>
     */
    public function threadComments(): HasMany
    {
        return $this->hasMany(self::class, 'root_id');
    }

    /**
     * @return BelongsTo<Comment, self>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Comment, self>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldestFirst();
    }

    /**
     * @return HasMany<Comment, self>
     */
    public function approvedReplies(): HasMany
    {
        return $this->replies()->approved();
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<CommentReaction, self>
     */
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
        if (empty($rootIds)) {
            return $query->whereRaw('1 = 0');
        }

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

    public function isRejected(): bool
    {
        return $this->status === CommentStatusEnum::REJECTED;
    }

    public function isDeleted(): bool
    {
        return $this->trashed();
    }

    public function shouldDisplayAsDeleted(): bool
    {
        return $this->trashed();
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->relationLoaded('user') && $this->user) {
            return $this->resolveUserDisplayName($this->user);
        }

        if ($this->user) {
            return $this->resolveUserDisplayName($this->user);
        }

        return filled($this->author_name) ? trim((string)$this->author_name) : 'کاربر';
    }

    public function getTimeLabelAttribute(): string
    {
        return $this->created_at?->diffForHumans() ?? '';
    }

    public function getRatingStarsAttribute(): int
    {
        if ($this->rating_percent === null) {
            return 0;
        }

        return max(0, min(5, (int)round($this->rating_percent / 20)));
    }

    public function getSafeContentAttribute(): string
    {
        return nl2br(e((string)$this->content));
    }

    protected function resolveUserDisplayName(User $user): string
    {
        $displayName = trim((string)($user->display_name ?? ''));

        if ($displayName !== '') {
            return $displayName;
        }

        $fullName = trim(collect([
            $user->first_name,
            $user->last_name,
        ])->filter(fn($value) => filled($value))->join(' '));

        return $fullName !== '' ? $fullName : 'کاربر';
    }
}
