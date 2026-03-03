<?php

namespace App\Http\Controllers\Admin\Project;

use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        return view('admin.projects.projects.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.projects.projects.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Project $project
     * @return View
     */
    public function edit(Project $project): View
    {
        return view('admin.projects.projects.edit', compact('project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        //
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function checkSlug(Request $request): JsonResponse
    {
        $slug = trim((string)$request->query('slug', ''));
        $ignoreId = $request->query('ignore');

        if ($slug === '') {
            return response()->json([
                'unique' => true,
                'suggested_slug' => null,
                'message' => null,
            ]);
        }

        $query = Project::query()->where('slug', $slug);
        if ($ignoreId) $query->where('id', '!=', $ignoreId);

        $exists = $query->exists();

        if (!$exists) {
            return response()->json([
                'unique' => true,
                'suggested_slug' => null,
                'message' => null,
            ]);
        }

        $base = $slug;
        $i = 2;
        $suggested = $base . '-' . $i;

        while (
        Project::query()
            ->where('slug', $suggested)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $i++;
            $suggested = $base . '-' . $i;
            if ($i > 999) break;
        }

        return response()->json([
            'unique' => false,
            'suggested_slug' => $suggested,
            'message' => 'این پیوند یکتا قبلاً استفاده شده.',
        ]);
    }
}
