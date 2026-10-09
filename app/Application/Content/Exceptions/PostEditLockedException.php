<?php

namespace App\Application\Content\Exceptions;

use App\Domain\Content\Models\Post;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class PostEditLockedException extends RuntimeException implements ShouldntReport
{
    public function __construct(public readonly Post $post, string $message)
    {
        parent::__construct($message);
    }

    public function render(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 423);
        }

        return response()->view('admin.content.posts.locked', [
            'post' => $this->post,
            'message' => $this->getMessage(),
        ], 423)->header('Cache-Control', 'no-store');
    }
}
