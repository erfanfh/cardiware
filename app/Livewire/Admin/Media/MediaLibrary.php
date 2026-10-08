<?php

namespace App\Livewire\Admin\Media;

use App\Application\Media\Actions\UploadImageToLibraryAction;
use App\Domain\Media\Enums\MediaFileDiskEnum;
use App\Domain\Media\Enums\MediaFileStatusEnum;
use App\Domain\Media\Enums\MediaFileTypeEnum;
use App\Domain\Media\Enums\MediaFileVisibilityEnum;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;

class MediaLibrary extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected string $paginationTheme = 'admin';

    private const MAX_UPLOAD_FILES = 20;

    public string $search = '';

    public string $mediaType = '';

    public string $status = '';

    public string $visibility = '';

    public string $sort = 'newest';

    public int $perPage = 24;

    public string $viewMode = 'grid';

    public ?int $selectedMediaFileId = null;

    public string $titleDraft = '';

    /** @var array<int, TemporaryUploadedFile> */
    public $uploads = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'mediaType' => ['as' => 'type', 'except' => ''],
        'status' => ['except' => ''],
        'visibility' => ['except' => ''],
        'sort' => ['except' => 'newest'],
        'perPage' => ['as' => 'per_page', 'except' => 24],
        'viewMode' => ['as' => 'view', 'except' => 'grid'],
    ];

    public function mount(): void
    {
        Gate::authorize('media.list');

        if (! in_array($this->viewMode, ['grid', 'list'], true)) {
            $this->viewMode = 'grid';
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMediaType(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedVisibility(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, [12, 24, 48, 96], true)) {
            $this->perPage = 24;
        }

        $this->resetPage();
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['grid', 'list'], true)) {
            $this->viewMode = $mode;
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'mediaType', 'status', 'visibility', 'sort');
        $this->resetPage();
    }

    public function selectMedia(int $mediaFileId): void
    {
        Gate::authorize('media.list');

        $media = MediaFile::query()->findOrFail($mediaFileId);

        $this->selectedMediaFileId = $media->id;
        $this->titleDraft = $media->title ?? '';
    }

    public function closeDetails(): void
    {
        $this->selectedMediaFileId = null;
        $this->titleDraft = '';
        $this->resetValidation('titleDraft');
    }

    public function saveTitle(): void
    {
        Gate::authorize('media.edit');

        $validated = $this->validate([
            'selectedMediaFileId' => ['required', 'integer', 'exists:media_files,id'],
            'titleDraft' => ['required', 'string', 'max:190'],
        ], [
            'titleDraft.required' => 'عنوان رسانه را وارد کنید.',
            'titleDraft.max' => 'عنوان رسانه نمی‌تواند بیشتر از ۱۹۰ نویسه باشد.',
        ]);

        MediaFile::query()
            ->whereKey($validated['selectedMediaFileId'])
            ->update(['title' => trim($validated['titleDraft'])]);

        $this->titleDraft = trim($validated['titleDraft']);
        $this->dispatch('media-library-notify', message: 'عنوان رسانه با موفقیت ذخیره شد.');
    }

    public function saveUploads(): void
    {
        Gate::authorize('media.create');

        try {
            $this->validate([
                'uploads' => ['required', 'array', 'min:1', 'max:'.self::MAX_UPLOAD_FILES],
                'uploads.*' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
            ], [
                'uploads.required' => 'حداقل یک تصویر برای آپلود انتخاب کنید.',
                'uploads.max' => 'در هر نوبت حداکثر ۲۰ تصویر قابل آپلود است.',
                'uploads.*.max' => 'حجم هر تصویر باید حداکثر ۵ مگابایت باشد.',
                'uploads.*.mimes' => 'فرمت تصاویر باید jpg، jpeg، png یا webp باشد.',
            ]);
        } catch (ValidationException $exception) {
            $this->dispatch('media-library-upload-failed');

            throw $exception;
        }

        $uploaded = [];
        $failed = [];

        foreach ($this->uploads as $file) {
            try {
                $uploaded[] = $this->uploadImage($file);
            } catch (Throwable $exception) {
                $failed[] = $file->getClientOriginalName();
                report($exception);
            }
        }

        $this->uploads = [];
        $this->resetValidation('uploads');
        $this->resetPage();

        if ($uploaded !== []) {
            /** @var MediaFile $lastMedia */
            $lastMedia = end($uploaded);
            $this->selectMedia($lastMedia->id);
        }

        $message = count($uploaded).' تصویر با موفقیت به کتابخانه اضافه شد.';

        if ($failed !== []) {
            $message .= ' آپلود '.count($failed).' فایل انجام نشد.';
            $this->addError('uploads', 'آپلود این فایل‌ها انجام نشد: '.implode('، ', $failed));
        }

        $this->dispatch(
            'media-library-uploaded',
            message: $message,
            uploadedCount: count($uploaded),
            failedCount: count($failed),
        );
    }

    protected function uploadImage(TemporaryUploadedFile $file): MediaFile
    {
        return app(UploadImageToLibraryAction::class)->execute(
            file: $file,
            createdBy: auth()->id(),
            visibility: MediaFileVisibilityEnum::PUBLIC,
            disk: MediaFileDiskEnum::PUBLIC,
        );
    }

    public function getSelectedMediaProperty(): ?MediaFile
    {
        if (! $this->selectedMediaFileId) {
            return null;
        }

        return MediaFile::query()
            ->with(['creator', 'primaryVariant', 'variants' => fn ($query) => $query->orderBy('width')])
            ->find($this->selectedMediaFileId);
    }

    /**
     * @return array{total: int, images: int, ready: int, total_bytes: int}
     */
    private function statistics(): array
    {
        return [
            'total' => MediaFile::query()->count(),
            'images' => MediaFile::query()->where('type', MediaFileTypeEnum::IMAGE)->count(),
            'ready' => MediaFile::query()->where('status', MediaFileStatusEnum::READY)->count(),
            'total_bytes' => (int) MediaFile::query()
                ->join('media_file_variants', 'media_file_variants.id', '=', 'media_files.primary_variant_id')
                ->sum('media_file_variants.byte_size'),
        ];
    }

    private function library(): LengthAwarePaginator
    {
        $query = MediaFile::query()
            ->with(['creator', 'primaryVariant', 'variants'])
            ->when($this->search !== '', function (Builder $query): void {
                $search = trim($this->search);

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('variants', fn (Builder $variantQuery) => $variantQuery
                            ->where('path', 'like', "%{$search}%")
                            ->orWhere('mime_type', 'like', "%{$search}%"));

                    if (ctype_digit($search)) {
                        $query->orWhereKey((int) $search);
                    }
                });
            })
            ->when($this->mediaType !== '', fn (Builder $query) => $query->where('type', $this->mediaType))
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->visibility !== '', fn (Builder $query) => $query->where('visibility', $this->visibility));

        match ($this->sort) {
            'oldest' => $query->oldest('id'),
            'title' => $query->orderBy('title')->orderByDesc('id'),
            'largest' => $query
                ->leftJoin('media_file_variants as primary_variants', 'primary_variants.id', '=', 'media_files.primary_variant_id')
                ->select('media_files.*')
                ->orderByDesc('primary_variants.byte_size')
                ->orderByDesc('media_files.id'),
            default => $query->latest('id'),
        };

        return $query->paginate($this->perPage);
    }

    public static function formatBytes(?int $bytes): string
    {
        if (! $bytes) {
            return '—';
        }

        $units = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت', 'ترابایت'];
        $value = (float) $bytes;
        $unit = 0;

        while ($value >= 1024 && $unit < count($units) - 1) {
            $value /= 1024;
            $unit++;
        }

        $precision = $value >= 10 || $unit === 0 ? 0 : 1;

        return number_format($value, $precision).' '.$units[$unit];
    }

    public function render(): View
    {
        return view('livewire.admin.media.media-library', [
            'mediaFiles' => $this->library(),
            'selectedMedia' => $this->selectedMedia,
            'statistics' => $this->statistics(),
            'typeOptions' => collect(MediaFileTypeEnum::cases())->mapWithKeys(fn ($type) => [
                $type->value => match ($type) {
                    MediaFileTypeEnum::IMAGE => 'تصویر',
                    MediaFileTypeEnum::VIDEO => 'ویدیو',
                    MediaFileTypeEnum::AUDIO => 'صوت',
                    MediaFileTypeEnum::DOCUMENT => 'سند',
                    MediaFileTypeEnum::OTHER => 'سایر',
                },
            ])->all(),
            'statusOptions' => collect(MediaFileStatusEnum::cases())->mapWithKeys(fn ($status) => [
                $status->value => match ($status) {
                    MediaFileStatusEnum::READY => 'آماده',
                    MediaFileStatusEnum::PROCESSING => 'در حال پردازش',
                    MediaFileStatusEnum::FAILED => 'ناموفق',
                },
            ])->all(),
        ]);
    }
}
