<?php

namespace App\Http\Controllers\Admin\Project;

use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Gate::authorize('project.list');

        $query = trim((string)$request->string('q'));

        if (mb_strlen($query) < 2) {
            return response()->json(['items' => []]);
        }

        $items = Project::query()
            ->select(['id', 'title'])
            ->where(function ($builder) use ($query) {
                $builder->where('title', 'like', '%' . $query . '%')
                    ->orWhere('id', $query);
            })
            ->orderBy('title')
            ->limit(50)
            ->get()
            ->map(fn(Project $project) => [
                'value' => (string)$project->id,
                'label' => "$project->title ($project->id)",
            ])
            ->values();

        return response()->json([
            'items' => $items,
        ]);
    }
}
