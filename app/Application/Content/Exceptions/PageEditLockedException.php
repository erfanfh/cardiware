<?php

namespace App\Application\Content\Exceptions;

use App\Domain\Content\Models\Page;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class PageEditLockedException extends RuntimeException implements ShouldntReport
{
    public function __construct(public readonly Page $page, string $message)
    {
        parent::__construct($message);
    }

    public function render(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 423);
        }

        return response()->view('admin.content.pages.locked', [
            'page' => $this->page,
            'message' => $this->getMessage(),
        ], 423)->header('Cache-Control', 'no-store');
    }
}
