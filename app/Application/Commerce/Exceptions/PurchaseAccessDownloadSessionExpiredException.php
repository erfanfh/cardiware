<?php

namespace App\Application\Commerce\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class PurchaseAccessDownloadSessionExpiredException extends RuntimeException
{
    public function render(Request $request): JsonResponse
    {
        return response()->json(
            [
                'message' => 'The download session has expired.',
                'code' => 'download_session_expired',
            ],
            Response::HTTP_CONFLICT
        );
    }
}
