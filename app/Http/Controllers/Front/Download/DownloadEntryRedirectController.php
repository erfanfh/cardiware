<?php

namespace App\Http\Controllers\Front\Download;

use App\Domain\Download\Models\DownloadEntry;

class DownloadEntryRedirectController
{
    public function __invoke(string $publicId)
    {
        $entry = DownloadEntry::query()
            ->where('public_id', $publicId)
            ->firstOrFail();

        if (!$entry->isLink())
            abort(404);

        $entry->increment('download_click_count');

        return redirect()->to($entry->content);
    }
}
