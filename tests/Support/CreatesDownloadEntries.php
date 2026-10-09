<?php

namespace Tests\Support;

use App\Domain\Content\Models\Post;
use App\Domain\Download\Models\DownloadEntry;
use App\Domain\Download\Models\DownloadSection;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Str;

trait CreatesDownloadEntries
{
    private function createDownloadEntry(array $attributes = []): DownloadEntry
    {
        $post = Post::withoutEvents(fn () => Post::query()->create([
            'author_id' => User::factory()->createQuietly()->id,
            'title' => 'Download test',
            'slug' => 'download-test-'.Str::random(8),
            'body' => 'Test content',
            'status' => 'active',
            'published_at' => now()->subMinute(),
        ]));
        $section = DownloadSection::query()->create(['post_id' => $post->id, 'title' => 'Files']);

        return $section->entries()->create(array_merge([
            'type' => 'link',
            'title' => 'Archive',
            'content' => 'https://files.example.test/folder%20name/archive.7z.001?mirror=2&signature=a%2Bb',
        ], $attributes));
    }
}
