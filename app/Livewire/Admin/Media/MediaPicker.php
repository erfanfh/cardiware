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
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;

class MediaPicker extends Component
{
    use WithPagination;
    use WithFileUploads;

    public ?int $selectedMediaFileId = null;
    public ?string $selectedUrl = null;

    /**
     * Data shown in preview panel (WP-like)
     * @var array{id:int|null,title:string|null,url:string|null,path:string|null,size_bytes:int|null,size_human:string|null}
     */
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

    public function mount(?int $selectedMediaFileId = null): void
    {
        $this->selectedMediaFileId = $selectedMediaFileId;

        $this->resetPage();
        $this->refreshSelected();
    }

    private function refreshSelected(): void
    {
        if (!$this->selectedMediaFileId) {
            $this->selectedUrl = null;
            $this->selectedInfo = [
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
            return;
        }

        $mediaFile = MediaFile::query()
            ->with(['primaryVariant', 'creator'])
            ->find($this->selectedMediaFileId);

        if (!$mediaFile) {
            $this->selectedUrl = null;
            $this->selectedInfo = [
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
            'uploaded_by' => ($mediaFile->creator?->name ?? '') . ' ' . ($mediaFile->creator?->family ?? ''),
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

    /**
     * ✅ When user picks a file, upload it immediately (WP-like)
     */
    public function updatedUpload(): void
    {
        $this->resetErrorBag('upload');

        if (!$this->upload) {
            return;
        }

        $this->validate([
            'upload' => 'required|file|max:5120|mimes:jpg,jpeg,png,webp',
        ]);

        // ✅ Auto-save immediately (no preview step)
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

            // ✅ clear upload input
            $this->clearUpload();

            // ✅ select new file and refresh preview info
            $this->selectedMediaFileId = $media->id;
            $this->refreshSelected();

            // ✅ refresh list to show newly added item
            $this->resetPage();

            // ✅ tell Alpine to switch to library (and maybe show success)
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

    public function select(int $mediaFileId): void
    {
        $this->selectedMediaFileId = $mediaFileId;
        $this->refreshSelected();

        // ✅ prevent grid skeleton re-mount
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
        $this->selectedInfo = [
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

        $this->resetPage();
    }

    public function getLibraryProperty()
    {
        return app(ListImageLibraryAction::class)->execute(
            search: $this->search,
            perPage: $this->perPage
        );
    }

    /**
     * @return void
     */
    public function unselect(): void
    {
        $this->selectedMediaFileId = null;
        $this->selectedUrl = null;

        $this->selectedInfo = [
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

        $this->skipRender();
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.admin.media.media-picker', [
            'library' => $this->library,
        ]);
    }
}
