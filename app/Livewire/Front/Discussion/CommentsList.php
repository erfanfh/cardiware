<?php

namespace App\Livewire\Front\Discussion;

use App\Domain\Discussion\Models\Comment;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CommentsList extends Component
{
    use WithPagination;

    public Model $commentable;

    public bool $isOpen = false;

    public string $sort = 'new';

    protected string $paginationTheme = 'tailwind';

    public function mount(Model $commentable): void
    {
        $this->commentable = $commentable;
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
    public function handleCommentCreated(string $commentableType, int $commentableId): void
    {
        if ($commentableType !== $this->commentable->getMorphClass()) {
            return;
        }

        if ((int)$commentableId !== (int)$this->commentable->getKey()) {
            return;
        }

        $this->resetPage();
    }

    #[Computed]
    public function totalComments(): int
    {
        return Comment::query()
            ->forCommentable($this->commentable)
            ->approved()
            ->root()
            ->count();
    }

    #[Computed]
    public function comments()
    {
        return Comment::query()
            ->forCommentable($this->commentable)
            ->approved()
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
            ->paginate(10);
    }

    public function render(): View
    {
        return view('livewire.front.discussion.comments-list');
    }
}
