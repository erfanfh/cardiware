<?php

namespace App\Application\Commerce\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class LauncherManifestChangedException extends RuntimeException
{
    public function __construct(private readonly int $currentRevision)
    {
        parent::__construct('The launcher manifest has changed.');
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'The launcher manifest has changed. Refresh the product manifest before retrying.',
            'code' => 'launcher_manifest_changed',
            'current_manifest_revision' => $this->currentRevision,
        ], Response::HTTP_CONFLICT);
    }
}
