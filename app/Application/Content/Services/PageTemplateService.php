<?php

namespace App\Application\Content\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PageTemplateService
{
    /**
     * Get all available page templates
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        $path = resource_path('views/templates');

        if (!File::exists($path)) {
            return [];
        }

        return collect(File::files($path))
            ->filter(fn($file) => Str::endsWith($file->getFilename(), '.blade.php'))
            ->mapWithKeys(function ($file) {
                $filename = Str::before($file->getFilename(), '.blade.php');

                return [
                    $filename => $this->resolveLabel($file->getRealPath(), $filename),
                ];
            })
            ->sort()
            ->toArray();
    }

    /**
     * Resolve template label from file
     */
    private function resolveLabel(string $path, string $fallback): string
    {
        $content = File::get($path);

        if (preg_match('/Template Name:\s*(.+)/i', $content, $matches)) {
            return trim($matches[1]);
        }

        return Str::headline($fallback);
    }

    /**
     * Check template exists
     */
    public function exists(?string $template): bool
    {
        if (!$template) {
            return false;
        }

        return File::exists(resource_path("views/templates/{$template}.blade.php"));
    }
}
