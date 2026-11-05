<?php

namespace App\Livewire\Front\Discussion;

use App\Application\Discussion\Actions\CreateCommentAction;
use App\Application\Discussion\Actions\LinkifyCommentContentAction;
use App\Application\Discussion\Actions\ResolveCommentAuthorBadgesAction;
use App\Application\Discussion\Actions\ToggleCommentReactionAction;
use App\Application\Discussion\DTOs\CreateCommentDataDTO;
use App\Application\Discussion\Support\CommentReactionIdentityResolver;
use App\Application\Discussion\Support\PendingCommentSessionStore;
use App\Domain\Discussion\Enums\CommentReactionTypeEnum;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Discussion\Models\CommentReaction;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use ValueError;

class CommentItem extends Component
{
    public int $commentId;

    public int $level = 0;

    public string $commentableType;

    public int $commentableId;

    public bool $repliesOpen = false;

    public bool $repliesLoaded = false;

    public bool $replyFormOpen = false;

    public string $replyContent = '';

    public string $replyAuthorName = '';

    public string $replyAuthorEmail = '';

    public ?string $replyGeneralError = null;

    public ?string $replySuccessMessage = null;

    public bool $repliesLoading = false;

    public bool $showReplies = true;

    public bool $allowReplySubmission = true;

    public array $notificationTargetPath = [];

    public function mount(
        int    $commentId,
        string $commentableType,
        int    $commentableId,
        int    $level = 0,
        bool   $showReplies = true,
        bool   $allowReplySubmission = true,
        array  $notificationTargetPath = [],
    ): void
    {
        $this->commentId = $commentId;
        $this->commentableType = $commentableType;
        $this->commentableId = $commentableId;
        $this->level = $level;
        $this->showReplies = $showReplies;
        $this->allowReplySubmission = $allowReplySubmission;
        $this->notificationTargetPath = $notificationTargetPath;
        if ($showReplies && in_array($commentId, $notificationTargetPath, true)) {
            $this->repliesOpen = true;
            $this->repliesLoaded = true;
        }
    }

    public function toggleReplies(): void
    {
        if (!$this->showReplies) {
            return;
        }

        if ($this->repliesOpen || $this->repliesLoading) {
            return;
        }

        $this->repliesOpen = true;
        $this->repliesLoading = true;

        if (!$this->repliesLoaded) {
            $this->repliesLoaded = true;
        }

        $this->dispatch('$refresh');

        usleep(180000);

        $this->repliesLoading = false;
    }

    public function submitReply(
        CreateCommentAction        $createCommentAction,
        PendingCommentSessionStore $pendingCommentSessionStore
    ): void
    {
        if (!$this->allowReplySubmission) {
            return;
        }

        $this->replyGeneralError = null;
        $this->replySuccessMessage = null;
        $this->resetErrorBag();

        try {
            $this->validate($this->rules(), $this->messages());

            $user = Auth::user();

            $dto = new CreateCommentDataDTO(
                commentable: $this->commentable,
                parentId: $this->commentId,
                userId: $user?->id,
                authorName: $user ? null : $this->replyAuthorName,
                authorEmail: $user ? null : $this->replyAuthorEmail,
                content: $this->replyContent,
                ratingPercent: null,
                ipAddress: request()->ip(),
                userAgent: request()->userAgent(),
            );

            $reply = $createCommentAction->execute($dto);

            if ($reply->isApproved()) {
                $this->repliesLoaded = true;
                $this->repliesOpen = true;

                $this->resetReplyState();
                $this->replyFormOpen = false;

                $this->dispatch(
                    'discussion-comment-replied-' . $this->commentId,
                    commentableType: $this->commentableType,
                    commentableId: $this->commentableId,
                );

                $this->dispatch(
                    'notify',
                    type: 'success',
                    message: 'پاسخ شما با موفقیت ثبت شد.',
                );
            } else {
                $pendingCommentSessionStore->add((int)$reply->id);

                $this->resetReplyState();
                $this->replySuccessMessage = 'پاسخ شما ثبت شد و پس از بررسی نمایش داده می‌شود.';
            }

            $this->dispatch(
                'discussion-comment-created',
                commentableType: $this->commentableType,
                commentableId: $this->commentableId,
                commentId: (int)$reply->id,
            );
        } catch (ValidationException $exception) {
            $errors = $exception->validator->errors();

            $this->resetErrorBag();
            $this->replyGeneralError = null;

            $fieldMap = [
                'replyContent' => ['replyContent', 'content'],
                'replyAuthorName' => ['replyAuthorName', 'author_name'],
                'replyAuthorEmail' => ['replyAuthorEmail', 'author_email'],
            ];

            foreach ($fieldMap as $targetField => $possibleKeys) {
                foreach ($possibleKeys as $key) {
                    if ($errors->has($key)) {
                        $this->addError($targetField, $errors->first($key));
                        break;
                    }
                }
            }

            foreach (['comments', 'commentable', 'parent_id'] as $generalKey) {
                if ($errors->has($generalKey)) {
                    $this->replyGeneralError = $errors->first($generalKey);
                    break;
                }
            }

            $this->replyFormOpen = true;

            return;
        } catch (\Throwable $throwable) {
            report($throwable);

            $this->replyGeneralError = 'ارسال پاسخ با خطا مواجه شد. دوباره تلاش کنید.';
        }
    }

    #[On('discussion-comment-replied-{commentId}')]
    public function refreshReplies(): void
    {
        $this->repliesLoaded = true;
        $this->repliesOpen = true;
    }

    #[Computed]
    public function guestPendingCommentIds(): array
    {
        return app(PendingCommentSessionStore::class)->all();
    }

    #[Computed]
    public function commentable(): Model
    {
        $class = Relation::getMorphedModel($this->commentableType) ?? $this->commentableType;

        return $class::query()->findOrFail($this->commentableId);
    }

    #[Computed]
    public function comment(): Comment
    {
        return Comment::query()
            ->visibleForFrontend($this->guestPendingCommentIds)
            ->addSelect([
                'viewer_reaction_type' => $this->viewerReactionSubquery(),
            ])
            ->with([
                'user' => fn ($query) => $query->withExists([
                    'vipSubscriptions as has_active_vip_subscription' => fn ($subscriptions) => $subscriptions
                        ->whereNull('revoked_at')
                        ->where('starts_at', '<=', now())
                        ->where('expires_at', '>', now()),
                ]),
                'user.avatar.primaryVariant',
                'user.role',
                'user.pointAccount:id,user_id,lifetime_xp',
            ])
            ->withCount('approvedReplies')
            ->findOrFail($this->commentId);
    }

    #[Computed]
    public function viewerReactionType(): ?string
    {
        $value = $this->comment->getAttribute(
            'viewer_reaction_type'
        );

        return filled($value)
            ? (string)$value
            : null;
    }

    #[Computed]
    public function replies()
    {
        if (!$this->repliesLoaded) {
            return collect();
        }

        return Comment::query()
            ->forCommentable($this->commentable)
            ->visibleForFrontend($this->guestPendingCommentIds)
            ->where('parent_id', $this->commentId)
            ->with([
                'user.avatar.primaryVariant',
                'user.role',
                'user.pointAccount:id,user_id,lifetime_xp',
            ])
            ->withCount('approvedReplies')
            ->oldestFirst()
            ->get();
    }

    #[Computed]
    public function approvedRepliesCount(): int
    {
        return (int)($this->comment->approved_replies_count ?? 0);
    }

    #[Computed]
    public function pendingVisibleRepliesCount(): int
    {
        return Comment::query()
            ->forCommentable($this->commentable)
            ->where('parent_id', $this->commentId)
            ->where('status', 'pending')
            ->when(
                Auth::check(),
                fn($query) => $query->where('user_id', Auth::id()),
                fn($query) => $query->whereIn('id', $this->guestPendingCommentIds)
            )
            ->count();
    }

    #[Computed]
    public function visibleRepliesCount(): int
    {
        return $this->approvedRepliesCount + $this->pendingVisibleRepliesCount;
    }

    #[Computed]
    public function hasVisibleReplies(): bool
    {
        return $this->visibleRepliesCount > 0;
    }

    #[Computed]
    public function authorBadges(): array
    {
        return app(ResolveCommentAuthorBadgesAction::class)->execute(
            comment: $this->comment,
            commentable: $this->commentable,
        );
    }

    /**
     * سطح باشگاه نویسنده (از سطح دوم به بالا تا برچسب «تازه‌وارد» روی همه نیفتد).
     */
    #[Computed]
    public function authorLevel(): ?\App\Domain\Loyalty\Models\LoyaltyLevel
    {
        $xp = (int) ($this->comment->user?->pointAccount?->lifetime_xp ?? 0);
        if ($xp <= 0) {
            return null;
        }

        $level = app(\App\Application\Loyalty\Actions\ResolveUserLevelAction::class)->execute($xp);

        return (int) $level->min_xp > 0 ? $level : null;
    }

    #[Computed]
    public function isVipAuthor(): bool
    {
        return (bool) $this->comment->user?->getAttribute('has_active_vip_subscription');
    }

    #[Computed]
    public function renderedContent(): string
    {
        return app(LinkifyCommentContentAction::class)->execute(
            $this->comment->safe_content
        );
    }

    public function isPendingVisibleToCurrentViewer(): bool
    {
        return $this->comment->isPendingForCurrentViewer($this->guestPendingCommentIds);
    }

    public function avatarText(): string
    {
        $name = trim((string)$this->comment->display_name);

        return $name !== '' ? mb_substr($name, 0, 1) : 'ک';
    }

    public function avatarColor(): string
    {
        $safeName = (string)$this->comment->display_name;

        $colors = [
            'bg-rose-100 text-rose-700',
            'bg-amber-100 text-amber-700',
            'bg-emerald-100 text-emerald-700',
            'bg-sky-100 text-sky-700',
            'bg-indigo-100 text-indigo-700',
            'bg-purple-100 text-purple-700',
        ];

        $index = 0;

        foreach (mb_str_split($safeName) as $char) {
            $index += mb_ord($char);
        }

        return $colors[$index % count($colors)];
    }

    public function toggleReaction(
        string                          $type,
        ToggleCommentReactionAction     $toggleCommentReactionAction,
        CommentReactionIdentityResolver $identityResolver,
    ): void
    {
        try {
            $reactionType = CommentReactionTypeEnum::from($type);
        } catch (ValueError) {
            return;
        }

        $rateLimitKey = implode(':', [
            'discussion-comment-reaction',
            $this->commentId,
            $identityResolver->rateLimitIdentity(),
        ]);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 20)) {
            $this->dispatch(
                'notify',
                type: 'warning',
                message: 'تعداد درخواست‌های شما بیش از حد مجاز است. کمی بعد دوباره تلاش کنید.',
            );

            return;
        }

        RateLimiter::hit(
            key: $rateLimitKey,
            decaySeconds: 60,
        );

        try {
            $toggleCommentReactionAction->execute(
                commentId: $this->commentId,
                requestedType: $reactionType,
                identity: $identityResolver->resolve(),
            );

            /*
             * Cache داخلی Computed Propertyهای همین کامپوننت پاک می‌شود
             * تا شمارنده‌ها و واکنش فعال دوباره خوانده شوند.
             */
            unset($this->comment);
            unset($this->viewerReactionType);

            $this->dispatch(
                'discussion-comment-reaction-updated',
                commentableType: $this->commentableType,
                commentableId: $this->commentableId,
                commentId: $this->commentId,
            );
        } catch (ModelNotFoundException) {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'این دیدگاه قابل لایک یا دیسلایک نیست.',
            );
        } catch (\Throwable $throwable) {
            report($throwable);

            $this->dispatch(
                'notify',
                type: 'error',
                message: 'ثبت واکنش با خطا مواجه شد. دوباره تلاش کنید.',
            );
        }
    }

    private function viewerReactionSubquery(): Builder
    {
        $identity = app(
            CommentReactionIdentityResolver::class
        )->resolve();

        return CommentReaction::query()
            ->select('type')
            ->whereColumn(
                'comment_reactions.comment_id',
                'comments.id'
            )
            ->when(
                $identity->isAuthenticated(),
                fn(Builder $query) => $query->where(
                    'user_id',
                    $identity->userId
                ),
                fn(Builder $query) => $query
                    ->whereNull('user_id')
                    ->where(
                        'ip_hash',
                        $identity->ipHash
                    )
            )
            ->limit(1);
    }

    protected function rules(): array
    {
        $isGuest = Auth::guest();

        return [
            'replyContent' => ['required', 'string', 'min:2', 'max:5000'],
            'replyAuthorName' => $isGuest ? ['required', 'string', 'max:255'] : ['nullable'],
            'replyAuthorEmail' => $isGuest ? ['required', 'email', 'max:255'] : ['nullable'],
        ];
    }

    protected function messages(): array
    {
        return [
            'replyContent.required' => 'متن پاسخ الزامی است.',
            'replyContent.min' => 'متن پاسخ خیلی کوتاه است.',
            'replyContent.max' => 'متن پاسخ بیش از حد طولانی است.',
            'replyAuthorName.required' => 'لطفا نام خود را وارد نمایید.',
            'replyAuthorEmail.required' => 'لطفا ایمیل خود را وارد نمایید.',
            'replyAuthorEmail.email' => 'ایمیل وارد شده معتبر نیست.',
        ];
    }

    protected function resetReplyState(): void
    {
        $this->replyContent = '';
        $this->replyAuthorName = '';
        $this->replyAuthorEmail = '';
        $this->replyGeneralError = null;
        $this->replySuccessMessage = null;
        $this->resetErrorBag();
    }

    public function render(): View
    {
        return view('livewire.front.discussion.comment-item');
    }
}
