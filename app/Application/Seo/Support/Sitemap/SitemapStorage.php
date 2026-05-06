<?php

namespace App\Application\Seo\Support\Sitemap;

use Illuminate\Support\Facades\Storage;

class SitemapStorage
{
    private const DISK = 'local';

    private string $activeDirectory = 'seo/sitemaps';

    private string $temporaryDirectory = 'seo/sitemaps-temp';

    private bool $writingToTemporaryDirectory = false;

    public function startTemporaryGeneration(): void
    {
        Storage::disk(self::DISK)->deleteDirectory($this->temporaryDirectory);
        Storage::disk(self::DISK)->makeDirectory($this->temporaryDirectory);

        $this->writingToTemporaryDirectory = true;
    }

    public function commitTemporaryGeneration(): void
    {
        Storage::disk(self::DISK)->deleteDirectory($this->activeDirectory);
        Storage::disk(self::DISK)->makeDirectory($this->activeDirectory);

        foreach (Storage::disk(self::DISK)->files($this->temporaryDirectory) as $file) {
            $filename = basename($file);

            Storage::disk(self::DISK)->move(
                $file,
                $this->activeDirectory . '/' . $filename
            );
        }

        Storage::disk(self::DISK)->deleteDirectory($this->temporaryDirectory);

        $this->writingToTemporaryDirectory = false;
    }

    public function discardTemporaryGeneration(): void
    {
        Storage::disk(self::DISK)->deleteDirectory($this->temporaryDirectory);

        $this->writingToTemporaryDirectory = false;
    }

    public function put(string $filename, string $content): void
    {
        Storage::disk(self::DISK)->put(
            $this->path($filename),
            $content
        );
    }

    public function exists(string $filename): bool
    {
        return Storage::disk(self::DISK)->exists(
            $this->activePath($filename)
        );
    }

    public function get(string $filename): string
    {
        return Storage::disk(self::DISK)->get(
            $this->activePath($filename)
        );
    }

    private function path(string $filename): string
    {
        $basePath = $this->writingToTemporaryDirectory
            ? $this->temporaryDirectory
            : $this->activeDirectory;

        return $basePath . '/' . ltrim($filename, '/');
    }

    private function activePath(string $filename): string
    {
        return $this->activeDirectory . '/' . ltrim($filename, '/');
    }
}
