<?php

namespace App\Http\Controllers\Front\Download;

use App\Domain\Download\Models\DownloadSection;
use Illuminate\Http\JsonResponse;

class CopySectionLinksController
{
    public function __invoke(DownloadSection $section): JsonResponse
    {
        $section->increment('copy_all_links_count');

        $links = $section->entries()
            ->where('type', 'link')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($entry) {
                return "{$entry->title}: {$entry->content}";
            })
            ->implode("\n");

        return response()->json([
            'text' => $links,
        ]);
    }
}
