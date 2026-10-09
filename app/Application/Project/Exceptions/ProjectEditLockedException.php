<?php

namespace App\Application\Project\Exceptions;

use App\Domain\Project\Models\Project;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class ProjectEditLockedException extends RuntimeException implements ShouldntReport
{
    public function __construct(public readonly Project $project, string $message)
    {
        parent::__construct($message);
    }

    public function render(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 423);
        }

        return response()->view('admin.projects.projects.locked', [
            'project' => $this->project,
            'message' => $this->getMessage(),
        ], 423)->header('Cache-Control', 'no-store');
    }
}
