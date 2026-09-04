<?php

namespace Tests\Unit\Media;

use App\Application\Media\Actions\ListImageLibraryAction;
use App\Domain\Media\Models\MediaFile;
use App\Livewire\Admin\Media\MediaPicker;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Mockery\MockInterface;
use Tests\TestCase;

class MediaPickerMultipleUploadTest extends TestCase
{
    public function test_multiple_selected_images_are_all_uploaded_and_the_last_one_is_preselected(): void
    {
        Storage::fake('local');
        config()->set('livewire.temporary_file_upload.disk', 'local');

        $this->mock(ListImageLibraryAction::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')
                ->andReturn(new LengthAwarePaginator([], 0, 24));
        });

        Livewire::test(TestableMediaPicker::class)
            ->assertSeeHtml('multiple')
            ->set('upload', [
                UploadedFile::fake()->image('first.jpg', 100, 100),
                UploadedFile::fake()->image('second.png', 100, 100),
            ])
            ->assertHasNoErrors()
            ->assertSet('upload', [])
            ->assertSet('selectedMediaFileId', 102)
            ->assertSet('pendingMediaFileId', 102)
            ->assertSet('uploadedFileNames', ['first.jpg', 'second.png'])
            ->assertSet('activeTab', 'library')
            ->assertDispatched('mfip-upload-saved', uploadedCount: 2, failedCount: 0);
    }

    public function test_an_invalid_file_in_the_batch_is_rejected_before_processing(): void
    {
        Storage::fake('local');
        config()->set('livewire.temporary_file_upload.disk', 'local');

        $this->mock(ListImageLibraryAction::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')
                ->andReturn(new LengthAwarePaginator([], 0, 24));
        });

        Livewire::test(TestableMediaPicker::class)
            ->set('upload', [
                UploadedFile::fake()->create('invalid.gif', 10, 'image/gif'),
            ])
            ->assertHasErrors(['upload.0' => 'mimes'])
            ->assertSet('uploadedFileNames', [])
            ->assertDispatched('mfip-upload-failed');
    }
}

class TestableMediaPicker extends MediaPicker
{
    public array $uploadedFileNames = [];

    protected function uploadImage(TemporaryUploadedFile $file): MediaFile
    {
        $this->uploadedFileNames[] = $file->getClientOriginalName();

        $media = new MediaFile();
        $media->id = 100 + count($this->uploadedFileNames);

        return $media;
    }

    protected function selectUploadedMedia(MediaFile $media): void
    {
        $this->selectedMediaFileId = $media->id;
        $this->pendingMediaFileId = $media->id;
    }
}
