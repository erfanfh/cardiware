<?php

namespace App\Livewire\Front\Discussion;

use App\Application\Discussion\Support\PendingCommentSessionStore;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ProductCommentsList extends Component
{
    use WithPagination;

    public string $commentableType;

    public int $commentableId;

    public Model $commentable;

    public bool $isOpen = false;

    public string $sort = 'new';

    public ?int $pendingScrollToCommentId = null;

    protected string $paginationTheme = 'front';

    public function mount(Model $commentable): void
    {
        $this->commentable = $commentable;
        $this->commentableId = (int)$commentable->getKey();
        $this->commentableType = $commentable->getMorphClass();
    }

    public function openComments(): void
    {
        $this->isOpen = true;
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    #[On('discussion-comment-created')]
    public function handleCommentCreated(
        string $commentableType,
        int    $commentableId,
        ?int   $commentId = null
    ): void
    {
        if ($commentableType !== $this->commentable->getMorphClass()) {
            return;
        }

        if ((int)$commentableId !== (int)$this->commentable->getKey()) {
            return;
        }

        $this->pendingScrollToCommentId = $commentId;
        $this->resetPage();

        if ($this->isOpen && $commentId) {
            $this->dispatch(
                'product-comments-refresh-requested',
                commentId: $commentId,
            );
        }
    }

    public function finishIncomingCommentTransition(): void
    {
        $this->pendingScrollToCommentId = null;
    }

    #[Computed]
    public function guestPendingCommentIds(): array
    {
        return app(PendingCommentSessionStore::class)->all();
    }

    #[Computed]
    public function totalComments(): int
    {
        $commentable = $this->commentable;

        return Cache::tags([
            CacheTag::COMMENTS,
            CacheTag::commentable($commentable->getMorphClass(), $commentable->getKey()),
        ])->remember(
            CacheKey::commentableApprovedCommentsCount(
                $commentable->getMorphClass(),
                $commentable->getKey()
            ),
            CacheTtl::medium(),
            fn() => Comment::query()
                ->forCommentable($commentable)
                ->approved()
                ->count()
        );
    }

    #[Computed]
    public function comments()
    {
        return Comment::query()
            ->forCommentable($this->commentable)
            ->visibleForFrontend($this->guestPendingCommentIds)
            ->root()
            ->with('user.avatar')
            ->withCount('approvedReplies')
            ->when(
                $this->sort === 'new',
                fn(Builder $query) => $query->latestFirst()
            )
            ->when(
                $this->sort === 'top',
                fn(Builder $query) => $query
                    ->orderByDesc('score')
                    ->orderByDesc('likes_count')
                    ->orderByDesc('approved_replies_count')
                    ->orderByDesc('id')
            )
            ->when(
                $this->sort === 'high',
                fn(Builder $query) => $query
                    ->orderByDesc('rating_percent')
                    ->orderByDesc('id')
            )
            ->paginate(5);
    }

    #[On('discussion-comment-reaction-updated')]
    public function handleCommentReactionUpdated(
        string $commentableType,
        int    $commentableId,
    ): void
    {
        if (
            $commentableType !== $this->commentable->getMorphClass() ||
            $commentableId !== (int)$this->commentable->getKey()
        ) {
            $this->skipRender();

            return;
        }

        /*
         * در حالت محبوب‌ترین باید ترتیب دیدگاه‌ها با score جدید
         * دوباره محاسبه شود.
         */
        if ($this->sort !== 'top') {
            $this->skipRender();
        }
    }

    public function render(): View
    {
        return view('livewire.front.discussion.product-comments-list');
    }
}
