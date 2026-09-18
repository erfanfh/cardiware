<?php

namespace Tests\Feature\Media;

use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Media\Enums\MediaFileDiskEnum;
use App\Domain\Media\Enums\MediaFileFormatEnum;
use App\Domain\Media\Enums\MediaFileStatusEnum;
use App\Domain\Media\Enums\MediaFileTypeEnum;
use App\Domain\Media\Enums\MediaFileVariantEnum;
use App\Domain\Media\Enums\MediaFileVisibilityEnum;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Media\Models\MediaFileVariant;
use App\Livewire\Admin\Media\MediaLibrary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaLibraryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_the_media_library_page(): void
    {
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin)
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('کتابخانه رسانه');
    }

    public function test_media_can_be_searched_selected_and_renamed(): void
    {
        Storage::fake('public');

        $admin = $this->createSuperAdmin();
        $stadium = $this->createImage($admin, 'تصویر ورزشگاه', 'media/images/stadium.webp', 1920, 1080, 2048);
        $this->createImage($admin, 'تصویر بازیکن', 'media/images/player.webp', 800, 800, 1024);

        Livewire::actingAs($admin)
            ->test(MediaLibrary::class)
            ->assertSee('تصویر ورزشگاه')
            ->assertSee('تصویر بازیکن')
            ->set('search', 'ورزشگاه')
            ->assertSee('تصویر ورزشگاه')
            ->assertDontSee('تصویر بازیکن')
            ->call('selectMedia', $stadium->id)
            ->assertSet('selectedMediaFileId', $stadium->id)
            ->assertSee('1920 × 1080 پیکسل')
            ->set('titleDraft', 'کاور جدید ورزشگاه')
            ->call('saveTitle')
            ->assertHasNoErrors()
            ->assertDispatched('media-library-notify');

        $this->assertDatabaseHas('media_files', [
            'id' => $stadium->id,
            'title' => 'کاور جدید ورزشگاه',
        ]);
    }

    private function createSuperAdmin(): User
    {
        $role = Role::factory()->create([
            'is_super_admin' => true,
            'is_default' => false,
        ]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function createImage(
        User $creator,
        string $title,
        string $path,
        int $width,
        int $height,
        int $byteSize,
    ): MediaFile {
        $media = MediaFile::query()->create([
            'type' => MediaFileTypeEnum::IMAGE,
            'visibility' => MediaFileVisibilityEnum::PUBLIC,
            'title' => $title,
            'created_by' => $creator->id,
            'status' => MediaFileStatusEnum::READY,
            'processed_at' => now(),
        ]);

        $variant = MediaFileVariant::query()->create([
            'media_file_id' => $media->id,
            'disk' => MediaFileDiskEnum::PUBLIC,
            'mime_type' => 'image/webp',
            'variant' => MediaFileVariantEnum::ORIGINAL,
            'format' => MediaFileFormatEnum::WEBP,
            'path' => $path,
            'byte_size' => $byteSize,
            'width' => $width,
            'height' => $height,
            'checksum' => hash('sha256', $path),
            'generated_at' => now(),
        ]);

        $media->update(['primary_variant_id' => $variant->id]);

        return $media->fresh();
    }
}
