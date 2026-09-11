<?php

namespace Tests\Unit\Media;

use App\Livewire\Admin\Media\MediaLibrary;
use PHPUnit\Framework\TestCase;

class MediaLibraryFormattingTest extends TestCase
{
    public function test_file_sizes_are_formatted_for_the_admin_interface(): void
    {
        $this->assertSame('—', MediaLibrary::formatBytes(null));
        $this->assertSame('512 بایت', MediaLibrary::formatBytes(512));
        $this->assertSame('1.5 کیلوبایت', MediaLibrary::formatBytes(1536));
        $this->assertSame('2.5 مگابایت', MediaLibrary::formatBytes(2_621_440));
    }
}
