<?php

namespace App\Livewire\Admin\Media;

use App\Application\Media\Actions\DeleteMediaFileFromLibraryAction;
use App\Application\Media\Actions\GetMediaFilePrimaryUrlAction;
use App\Application\Media\Actions\ListImageLibraryAction;
use App\Application\Media\Actions\UploadImageToLibraryAction;
use App\Domain\Media\Enums\MediaFileDiskEnum;
use App\Domain\Media\Enums\MediaFileVisibilityEnum;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Support\Facades\Storage;
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
    use WithFileUploads;

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

    /** @var TemporaryUploadedFile|null */
    public $upload = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public ?int $pendingMediaFileId = null;

    #[On('mfip-open-singleton')]
    public function openSingleton(string $itemKey, ?int $selectedMediaFileId = null): void
    {
        $this->itemKey = $itemKey;
        $this->selectedMediaFileId = $selectedMediaFileId;
        $this->pendingMediaFileId = $selectedMediaFileId;
        $this->opened = true;

        $this->resetPage();
        $this->refreshSelected();

        $this->dispatch('mfip-modal-opened');
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
            ->with(['primaryVariant', 'creator'])
            ->find($this->selectedMediaFileId);

        if (!$mediaFile) {
            $this->selectedUrl = null;
            $this->selectedInfo = $this->emptySelectedInfo();
            return;
        }

        $url = app(GetMediaFilePrimaryUrlAction::class)->execute($mediaFile);
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

        $this->selectedInfo = [
            'id' => $mediaFile->id,
            'title' => $mediaFile->title ?? null,
            'url' => $url,
            'path' => $path,
            'size_bytes' => $sizeBytes,
            'size_human' => $sizeBytes ? $this->humanBytes($sizeBytes) : null,
            'uploaded_at' => jalali_date($mediaFile->processed_at),
            'uploaded_by' => trim(($mediaFile->creator?->name ?? '') . ' ' . ($mediaFile->creator?->family ?? '')),
            'width' => $mediaFile->primaryVariant?->width,
            'height' => $mediaFile->primaryVariant?->height,
        ];
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
        $this->resetErrorBag('upload');

        if (!$this->upload) {
            return;
        }

        $this->validate([
            'upload' => 'required|file|max:5120|mimes:jpg,jpeg,png,webp',
        ]);

        $this->saveUpload();
    }

    public function saveUpload(): void
    {
        $this->validate([
            'upload' => 'required|file|max:5120|mimes:jpg,jpeg,png,webp',
        ]);

        try {
            $media = app(UploadImageToLibraryAction::class)->execute(
                file: $this->upload,
                createdBy: auth()->id(),
                visibility: MediaFileVisibilityEnum::PUBLIC,
                disk: MediaFileDiskEnum::PUBLIC
            );

            $this->clearUpload();

            $this->selectedMediaFileId = $media->id;
            $this->pendingMediaFileId = $media->id;
            $this->refreshSelected();
            $this->resetPage();

            $this->dispatchSelectionChanged();

            $this->dispatch('mfip-switch-tab', tab: 'library');
            $this->dispatch('mfip-upload-saved');
        } catch (Throwable $e) {
            $this->addError('upload', $e->getMessage());
        }
    }

    public function clearUpload(): void
    {
        $this->upload = null;
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

        $this->opened = false;
        $this->dispatch('mfip-close-modal');

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
            mediaPath: $this->selectedInfo['url'], // path key, full URL value
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
