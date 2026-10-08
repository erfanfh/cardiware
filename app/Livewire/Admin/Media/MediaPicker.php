<?php

namespace App\Livewire\Admin\Media;

use App\Application\Media\Actions\DeleteMediaFileFromLibraryAction;
use App\Application\Media\Actions\GetMediaFileUrlAction;
use App\Application\Media\Actions\ListImageLibraryAction;
use App\Application\Media\Actions\UploadImageToLibraryAction;
use App\Domain\Media\Enums\MediaFileDiskEnum;
use App\Domain\Media\Enums\MediaFileVariantEnum;
use App\Domain\Media\Enums\MediaFileVisibilityEnum;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;

class MediaPicker extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'admin';
    use WithFileUploads;

    private const MAX_UPLOAD_FILES = 20;

    public ?string $itemKey = null;
    public bool $opened = false;

    public ?int $selectedMediaFileId = null;
    public ?string $selectedUrl = null;

    public array $selectedInfo = [
        'id' => null,
        'title' => null,
        'url' => null,
        'path' => null,
        'size_bytes' => null,
        'size_human' => null,
        'uploaded_at' => null,
        'uploaded_by' => null,
        'width' => null,
        'height' => null,
    ];

    public string $search = '';
    public int $perPage = 24;

    /** @var array<int, TemporaryUploadedFile> */
    public $upload = [];

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public ?int $pendingMediaFileId = null;

    public string $activeTab = 'upload';

    #[On('mfip-open-singleton')]
    public function openSingleton(string $itemKey, ?int $selectedMediaFileId = null): void
    {
        $this->itemKey = $itemKey;
        $this->selectedMediaFileId = $selectedMediaFileId;
        $this->pendingMediaFileId = $selectedMediaFileId;

        $this->activeTab = $selectedMediaFileId ? 'library' : 'upload';

        $this->resetPage();
        $this->refreshSelected();

        $this->opened = true;

        $this->dispatch('mfip-grid-refresh');
    }

    public function previewSelection(int $mediaFileId): void
    {
        $this->pendingMediaFileId = $mediaFileId;
        $this->selectedMediaFileId = $mediaFileId;

        $this->refreshSelected();
    }

    public function closeModal(): void
    {
        $this->opened = false;
        $this->dispatch('mfip-close-modal');
    }

    private function emptySelectedInfo(): array
    {
        return [
            'id' => null,
            'title' => null,
            'url' => null,
            'path' => null,
            'variants' => [],
            'size_bytes' => null,
            'size_human' => null,
            'uploaded_at' => null,
            'uploaded_by' => null,
            'width' => null,
            'height' => null,
        ];
    }

    private function refreshSelected(): void
    {
        if (!$this->selectedMediaFileId) {
            $this->selectedUrl = null;
            $this->selectedInfo = $this->emptySelectedInfo();
            return;
        }

        $mediaFile = MediaFile::query()
            ->with(['variants', 'primaryVariant', 'creator'])
            ->find($this->selectedMediaFileId);

        if (!$mediaFile) {
            $this->selectedUrl = null;
            $this->selectedInfo = $this->emptySelectedInfo();
            return;
        }

        $url = app(GetMediaFileUrlAction::class)->execute($mediaFile);
        $this->selectedUrl = $url;

        $path = $mediaFile->primaryVariant?->path;
        $sizeBytes = null;

        try {
            $disk = $mediaFile->primaryVariant?->disk?->value;

            if ($disk && $path) {
                $sizeBytes = Storage::disk($disk)->size($path);
            }
        } catch (Throwable) {
            $sizeBytes = null;
        }

        $variants = $this->buildVariantUrls($mediaFile);

        $this->selectedInfo = [
            'id' => $mediaFile->id,
            'title' => $mediaFile->title ?? null,
            'url' => $url,
            'path' => $path,
            'variants' => $variants,
            'size_bytes' => $sizeBytes,
            'size_human' => $sizeBytes ? $this->humanBytes($sizeBytes) : null,
            'uploaded_at' => jalali_date($mediaFile->processed_at),
            'uploaded_by' => trim(($mediaFile->creator?->first_name ?? '') . ' ' . ($mediaFile->creator?->last_name ?? '')),
            'width' => $mediaFile->primaryVariant?->width,
            'height' => $mediaFile->primaryVariant?->height,
        ];
    }

    private function buildVariantUrls(MediaFile $mediaFile): array
    {
        $variants = [
            MediaFileVariantEnum::ORIGINAL->value => null,
            MediaFileVariantEnum::THUMBNAIL->value => null,
            MediaFileVariantEnum::MEDIUM->value => null,
            MediaFileVariantEnum::MEDIUM_LARGE->value => null,
            MediaFileVariantEnum::LARGE->value => null,
        ];

        foreach ($mediaFile->variants as $variant) {
            $variantName = $variant->variant instanceof MediaFileVariantEnum
                ? $variant->variant->value
                : (string)$variant->variant;

            if (!array_key_exists($variantName, $variants)) {
                continue;
            }

            $disk = $variant->disk instanceof MediaFileDiskEnum
                ? $variant->disk->value
                : (string)$variant->disk;

            if (!$disk || !$variant->path) {
                continue;
            }

            $variants[$variantName] = Storage::disk($disk)->url($variant->path);
        }

        $fallbackUrl = app(GetMediaFileUrlAction::class)->execute($mediaFile);

        $variants[MediaFileVariantEnum::ORIGINAL->value] ??= $fallbackUrl;
        $variants[MediaFileVariantEnum::MEDIUM->value] ??= $fallbackUrl;
        $variants[MediaFileVariantEnum::MEDIUM_LARGE->value] ??= $variants[MediaFileVariantEnum::LARGE->value] ?? $fallbackUrl;
        $variants[MediaFileVariantEnum::THUMBNAIL->value] ??= $variants[MediaFileVariantEnum::MEDIUM->value] ?? $fallbackUrl;
        $variants[MediaFileVariantEnum::LARGE->value] ??= $variants[MediaFileVariantEnum::MEDIUM_LARGE->value] ?? $fallbackUrl;

        return array_filter($variants);
    }

    private function humanBytes(int $bytes): string
    {
        $units = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت', 'ترابایت'];
        $i = 0;
        $n = (float)$bytes;

        while ($n >= 1024 && $i < count($units) - 1) {
            $n /= 1024;
            $i++;
        }

        return rtrim(rtrim(number_format($n, 0, '.', ''), '0'), '.') . ' ' . $units[$i];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedUpload(): void
    {
        $this->resetErrorBag();

        if (empty($this->upload)) {
            return;
        }

        $this->saveUpload();
    }

    public function saveUpload(): void
    {
        try {
            $this->validate([
                'upload' => ['required', 'array', 'min:1', 'max:'.self::MAX_UPLOAD_FILES],
                'upload.*' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
            ], [
                'upload.max' => 'در هر نوبت حداکثر '.self::MAX_UPLOAD_FILES.' تصویر قابل آپلود است.',
                'upload.*.file' => 'یکی از موارد انتخاب‌شده فایل معتبری نیست.',
                'upload.*.max' => 'حجم هر تصویر باید حداکثر ۵ مگابایت باشد.',
                'upload.*.mimes' => 'فرمت تصاویر باید jpg، jpeg، png یا webp باشد.',
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('mfip-upload-failed');

            throw $e;
        }

        $uploadedMedia = [];
        $failedUploads = [];

        foreach ($this->upload as $file) {
            try {
                $uploadedMedia[] = $this->uploadImage($file);
            } catch (Throwable $e) {
                $failedUploads[] = $file->getClientOriginalName().': '.$e->getMessage();
            }
        }

        $this->clearUpload();

        if ($uploadedMedia !== []) {
            /** @var MediaFile $media */
            $media = end($uploadedMedia);
            $this->selectUploadedMedia($media);
        }

        if ($failedUploads !== []) {
            $this->addError(
                'upload',
                'آپلود بعضی تصاویر انجام نشد: '.implode('، ', $failedUploads)
            );
        } elseif ($uploadedMedia !== []) {
            $this->activeTab = 'library';

            $this->dispatch('mfip-switch-tab', tab: 'library');
        }

        $this->dispatch(
            'mfip-upload-saved',
            uploadedCount: count($uploadedMedia),
            failedCount: count($failedUploads),
        );
    }

    protected function uploadImage(TemporaryUploadedFile $file): MediaFile
    {
        return app(UploadImageToLibraryAction::class)->execute(
            file: $file,
            createdBy: auth()->id(),
            visibility: MediaFileVisibilityEnum::PUBLIC,
            disk: MediaFileDiskEnum::PUBLIC
        );
    }

    protected function selectUploadedMedia(MediaFile $media): void
    {
        $this->selectedMediaFileId = $media->id;
        $this->pendingMediaFileId = $media->id;
        $this->refreshSelected();
        $this->resetPage();
    }

    public function clearUpload(): void
    {
        $this->upload = [];
        $this->resetErrorBag('upload');
    }

    public function confirmSelection(): void
    {
        if (!$this->pendingMediaFileId) {
            return;
        }

        $this->selectedMediaFileId = $this->pendingMediaFileId;
        $this->refreshSelected();

        $this->dispatchSelectionChanged();

        $this->opened = false;
        $this->dispatch('mfip-close-modal');

        $this->skipRender();
    }

    public function unselect(): void
    {
        $this->pendingMediaFileId = null;
        $this->selectedMediaFileId = null;
        $this->selectedUrl = null;
        $this->selectedInfo = $this->emptySelectedInfo();

        $this->dispatchSelectionCleared();

        $this->skipRender();
    }

    public function updateSelectedTitle(string $title): void
    {
        $title = trim($title);

        $this->validate([
            'selectedMediaFileId' => 'required|integer',
        ]);

        $mediaFile = MediaFile::find($this->selectedMediaFileId);

        if (!$mediaFile) {
            $this->addError('selectedMediaFileId', 'فایل انتخاب‌شده پیدا نشد.');
            return;
        }

        $mediaFile->title = $title !== '' ? $title : null;
        $mediaFile->save();

        $this->selectedInfo['title'] = $mediaFile->title;

        $this->dispatchSelectionChanged();

        $this->skipRender();
    }

    /**
     * @throws Throwable
     */
    public function deleteSelected(): void
    {
        $this->validate([
            'selectedMediaFileId' => 'required|integer',
        ]);

        $mediaFile = MediaFile::query()
            ->with(['variants', 'primaryVariant'])
            ->find($this->selectedMediaFileId);

        if (!$mediaFile) {
            $this->addError('selectedMediaFileId', 'فایل انتخاب‌شده پیدا نشد.');
            return;
        }

        app(DeleteMediaFileFromLibraryAction::class)->execute($mediaFile);

        $this->selectedMediaFileId = null;
        $this->selectedUrl = null;
        $this->selectedInfo = $this->emptySelectedInfo();

        $this->dispatchSelectionCleared();
        $this->resetPage();
    }

    public function getLibraryProperty()
    {
        return app(ListImageLibraryAction::class)->execute(
            search: $this->search,
            perPage: $this->perPage
        );
    }

    private function dispatchSelectionChanged(): void
    {
        $this->dispatch(
            'mfip-selected',
            itemKey: $this->itemKey,
            mediaId: $this->selectedInfo['id'],
            mediaPath: $this->selectedInfo['variants'][MediaFileVariantEnum::ORIGINAL->value] ?? $this->selectedInfo['url'],
            mediaTitle: $this->selectedInfo['title'],
            mediaVariants: $this->selectedInfo['variants'] ?? [],
        );
    }

    private function dispatchSelectionCleared(): void
    {
        $this->dispatch(
            'mfip-cleared',
            itemKey: $this->itemKey,
        );
    }

    public function render(): View
    {
        return view('livewire.admin.media.media-picker', [
            'library' => $this->library,
        ]);
    }
}
