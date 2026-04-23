<?php

namespace App\Livewire\Front\Discussion;

use App\Application\Discussion\Actions\CreateCommentAction;
use App\Application\Discussion\DTOs\CreateCommentDataDTO;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class SendCommentForm extends Component
{
    public string $commentableType;

    public int $commentableId;

    public ?int $parentId = null;

    public string $authorName = '';

    public string $authorEmail = '';

    public string $content = '';

    public ?int $ratingPercent = null;

    public string $website = '';

    public int $startedAt;

    public ?string $generalError = null;

    public ?string $successMessage = null;

    public function mount(Model $commentable, ?int $parentId = null): void
    {
        $this->commentableType = $commentable->getMorphClass();
        $this->commentableId = (int)$commentable->getKey();
        $this->parentId = $parentId;
        $this->startedAt = now()->timestamp;

        if (Auth::check()) {
            $this->authorName = '';
            $this->authorEmail = '';
        }
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['authorName', 'authorEmail', 'content', 'ratingPercent'], true)) {
            $this->resetValidation($property);
        }

        if ($this->generalError && in_array($property, ['authorName', 'authorEmail', 'content', 'ratingPercent'], true)) {
            $this->generalError = null;
        }
    }

    public function setRating(int $value): void
    {
        $currentStars = $this->ratingPercent ? (int)($this->ratingPercent / 20) : 0;

        $this->ratingPercent = $currentStars === $value ? null : $value * 20;

        $this->resetValidation('ratingPercent');
        $this->generalError = null;
    }

    public function submit(CreateCommentAction $createCommentAction): void
    {
        $this->generalError = null;
        $this->successMessage = null;
        $this->resetErrorBag();

        try {
            $this->validate($this->rules(), $this->messages());

            $user = Auth::user();

            $dto = new CreateCommentDataDTO(
                commentable: $this->commentable,
                parentId: $this->parentId,
                userId: $user?->id,
                authorName: $user ? null : $this->authorName,
                authorEmail: $user ? null : $this->authorEmail,
                content: $this->content,
                ratingPercent: $this->ratingPercent,
                ipAddress: request()->ip(),
                userAgent: request()->userAgent(),
            );

            $comment = $createCommentAction->execute($dto);

            $this->resetFormState();

            if ($comment->isApproved()) {
                $this->successMessage = 'دیدگاه شما با موفقیت ثبت شد.';
            } else {
                $this->successMessage = 'دیدگاه شما ثبت شد و پس از بررسی نمایش داده می‌شود.';
            }

            $this->dispatch(
                'discussion-comment-created',
                commentableType: $this->commentableType,
                commentableId: $this->commentableId,
            );

            $this->dispatch(
                'notify',
                type: 'success',
                message: $this->successMessage,
            );

            $this->dispatch('discussion-comment-form-submitted');
        } catch (ValidationException $exception) {
            $errors = $exception->validator->errors();

            if ($errors->has('comments')) {
                $this->generalError = $errors->first('comments');
                return;
            }

            if ($errors->has('commentable')) {
                $this->generalError = $errors->first('commentable');
                return;
            }

            if ($errors->has('parent_id')) {
                $this->generalError = $errors->first('parent_id');
                return;
            }

            throw $exception;
        } catch (\Throwable $throwable) {
            report($throwable);

            $this->generalError = 'ارسال دیدگاه با خطا مواجه شد. دوباره تلاش کنید.';
        }
    }

    #[On('discussion-set-parent-comment')]
    public function setParentComment(?int $parentId = null): void
    {
        $this->parentId = $parentId;
    }

    #[Computed]
    public function commentable(): Model
    {
        $class = Relation::getMorphedModel($this->commentableType) ?? $this->commentableType;

        return $class::query()->findOrFail($this->commentableId);
    }

    #[Computed]
    public function currentRatingStars(): int
    {
        return $this->ratingPercent ? (int)($this->ratingPercent / 20) : 0;
    }

    protected function rules(): array
    {
        $isGuest = Auth::guest();

        return [
            'content' => ['required', 'string', 'min:2', 'max:5000'],
            'authorName' => $isGuest ? ['required', 'string', 'max:255'] : ['nullable'],
            'authorEmail' => $isGuest ? ['required', 'email', 'max:255'] : ['nullable'],
            'ratingPercent' => ['nullable', 'integer', 'min:20', 'max:100'],
            'website' => ['nullable', 'max:0'],
            'startedAt' => ['required', 'integer'],
        ];
    }

    protected function messages(): array
    {
        return [
            'content.required' => 'متن دیدگاه الزامی است.',
            'content.min' => 'متن دیدگاه خیلی کوتاه است.',
            'content.max' => 'متن دیدگاه بیش از حد طولانی است.',
            'authorName.required' => 'لطفا نام خود را وارد نمایید.',
            'authorEmail.required' => 'لطفا ایمیل خود را وارد نمایید.',
            'authorEmail.email' => 'ایمیل وارد شده معتبر نیست.',
            'ratingPercent.integer' => 'امتیاز وارد شده معتبر نیست.',
            'ratingPercent.min' => 'حداقل امتیاز باید 1 ستاره باشد.',
            'ratingPercent.max' => 'حداکثر امتیاز 5 ستاره است.',
            'website.max' => 'ارسال دیدگاه معتبر نیست.',
            'startedAt.required' => 'ارسال دیدگاه معتبر نیست.',
        ];
    }

    protected function resetFormState(): void
    {
        $this->content = '';
        $this->ratingPercent = null;
        $this->website = '';
        $this->startedAt = now()->timestamp;
        $this->generalError = null;
        $this->resetErrorBag();

        if (Auth::guest()) {
            $this->authorName = '';
            $this->authorEmail = '';
        }
    }

    public function render(): View
    {
        return view('livewire.front.discussion.send-comment-form');
    }
}
