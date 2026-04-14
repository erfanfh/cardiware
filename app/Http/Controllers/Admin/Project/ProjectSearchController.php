<?php

namespace App\Http\Controllers\Admin\Project;

use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string)$request->string('q'));

        if (mb_strlen($query) < 2) {
            return response()->json(['items' => []]);
        }

        $items = Project::query()
            ->select(['id', 'title'])
            ->where('title', 'like', '%' . $query . '%')
            ->orderBy('title')
            ->limit(20)
            ->get()
            ->map(fn(Project $project) => [
                'value' => (string)$project->id,
                'label' => $project->title,
            ])
            ->values();

        return response()->json([
            'items' => $items,
        ]);
    }
}
