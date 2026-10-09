<?php

namespace App\Http\Controllers\Front\Download;

use App\Domain\Download\Models\DownloadEntry;
use App\Infrastructure\Support\Counters\DownloadClickCounter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class TrackDownloadEntryClickController
{
    public function __invoke(Request $request, string $publicId, DownloadClickCounter $counter): Response
    {
        $entry = DownloadEntry::query()
            ->select(['id', 'type'])
            ->where('public_id', $publicId)
            ->whereHas('section.post')
            ->firstOrFail();

        abort_unless($entry->isLink(), 404);

        try {
            $counter->record($entry->id, (string) $request->ip());
        } catch (Throwable $exception) {
            report($exception);
        }

        return response()->noContent()->header('Cache-Control', 'private, no-store, max-age=0');
    }
}
