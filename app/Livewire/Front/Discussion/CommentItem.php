<?php

namespace App\Livewire\Front\Discussion;

use App\Application\Discussion\Actions\CreateCommentAction;
use App\Application\Discussion\DTOs\CreateCommentDataDTO;
use App\Application\Discussion\Support\PendingCommentSessionStore;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

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

    public function mount(
        int    $commentId,
        string $commentableType,
        int    $commentableId,
        int    $level = 0
    ): void
    {
        $this->commentId = $commentId;
        $this->commentableType = $commentableType;
        $this->commentableId = $commentableId;
        $this->level = $level;
    }

    public function toggleReplies(): void
    {
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
            );
        } catch (ValidationException $exception) {
            $errors = $exception->validator->errors();

            if ($errors->has('comments')) {
                $this->replyGeneralError = $errors->first('comments');
                return;
            }

            if ($errors->has('commentable')) {
                $this->replyGeneralError = $errors->first('commentable');
                return;
            }

            if ($errors->has('parent_id')) {
                $this->replyGeneralError = $errors->first('parent_id');
                return;
            }

            throw $exception;
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
            ->with(['user.avatar'])
            ->withCount('approvedReplies')
            ->findOrFail($this->commentId);
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
            ->with(['user.avatar'])
            ->withCount('approvedReplies')
            ->oldestFirst()
            ->get();
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
