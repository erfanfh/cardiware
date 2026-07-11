<?php

namespace App\Infrastructure\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

abstract class ApiException extends RuntimeException implements ShouldntReport
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        string                  $message,
        private readonly string $errorCode,
        private readonly int    $statusCode,
        private readonly array  $details = [],
    )
    {
        parent::__construct($message);
    }

    public function render(Request $request): JsonResponse|bool
    {
        if (!$request->is('api/*') && !$request->expectsJson()) {
            return false;
        }

        $response = [
            'message' => $this->getMessage(),
            'code' => $this->errorCode,
        ];

        if ($this->details !== []) {
            $response['details'] = $this->details;
        }

        return response()->json(
            data: $response,
            status: $this->statusCode,
        );
    }
}
