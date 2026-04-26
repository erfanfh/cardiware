<?php

namespace App\Http\Controllers\Front\Download;

use App\Domain\Download\Models\DownloadEntry;
use App\Infrastructure\Support\Counters\BufferedCounter;

class DownloadEntryRedirectController
{
    public function __invoke(string $publicId)
    {
        $entry = DownloadEntry::query()
            ->select(['id', 'public_id', 'type', 'content'])
            ->where('public_id', $publicId)
            ->firstOrFail();

        if (!$entry->isLink()) {
            abort(404);
        }

        BufferedCounter::increment(
            namespace: 'download_clicks',
            type: 'DownloadEntry',
            id: $entry->id,
        );

        return redirect()->away($entry->content);
    }
}
