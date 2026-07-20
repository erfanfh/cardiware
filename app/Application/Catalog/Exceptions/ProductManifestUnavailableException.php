<?php

namespace App\Application\Catalog\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class ProductManifestUnavailableException extends RuntimeException
{
    public function __construct(private readonly string $reason)
    {
        parent::__construct($reason);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json(
            [
                'message' => $this->getMessage() ?: 'The product manifest is currently unavailable.',
                'code' => 'product_manifest_unavailable',
            ],
            Response::HTTP_SERVICE_UNAVAILABLE
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'manifest_unavailable_reason' => $this->reason,
        ];
    }
}
