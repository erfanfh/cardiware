<?php

namespace App\Application\Video\Exceptions;

use App\Domain\Video\Models\Video;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class VideoEditLockedException extends RuntimeException implements ShouldntReport
{
    public function __construct(public readonly Video $video, string $message)
    {
        parent::__construct($message);
    }

    public function render(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 423);
        }

        return response()->view('admin.video.videos.locked', [
            'video' => $this->video,
            'message' => $this->getMessage(),
        ], 423)->header('Cache-Control', 'no-store');
    }
}
