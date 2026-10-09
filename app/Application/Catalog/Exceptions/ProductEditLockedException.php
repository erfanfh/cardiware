<?php

namespace App\Application\Catalog\Exceptions;

use App\Domain\Catalog\Models\Product;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class ProductEditLockedException extends RuntimeException implements ShouldntReport
{
    public function __construct(public readonly Product $product, string $message)
    {
        parent::__construct($message);
    }

    public function render(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 423);
        }

        return response()->view('admin.catalog.products.locked', [
            'product' => $this->product,
            'message' => $this->getMessage(),
        ], 423)->header('Cache-Control', 'no-store');
    }
}
