<?php

namespace App\Application\Launcher\Actions;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\URL;

class GenerateTemporaryLauncherDownloadUrlAction
{
    public function execute(
        int $purchaseAccessId,
        string $contentType,
        int $contentId,
        int $fileId,
        string $downloadSessionId,
        int $manifestRevision,
        CarbonInterface $expiresAt,
    ): string {
        return URL::temporarySignedRoute(
            name: 'downloads.launcher.session-file',
            expiration: $expiresAt,
            parameters: [
                'purchaseAccess' => $purchaseAccessId,
                'contentType' => $contentType,
                'content' => $contentId,
                'file' => $fileId,
                'downloadSessionId' => $downloadSessionId,
                'manifestRevision' => $manifestRevision,
            ],
        );
    }
}
