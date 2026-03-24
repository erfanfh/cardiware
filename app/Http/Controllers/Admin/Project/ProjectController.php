<?php

namespace App\Http\Controllers\Admin\Project;

use App\Application\Project\Actions\CreateProjectAction;
use App\Application\Project\Actions\UpdateProjectAction;
use App\Domain\Project\Enums\ProjectStatusEnum;
use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Project\ProjectRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        $projects = Project::latest()->paginate(10);
        $statuses = [];
        foreach (ProjectStatusEnum::cases() as $status) {
            $statuses[$status->value] = $status->label();
        }

        return view('admin.projects.projects.index', compact('projects', 'statuses'));
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
     *
     * @param UpdateProjectRequest $request
     * @param CreateProjectAction $action
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(UpdateProjectRequest $request, CreateProjectAction $action): RedirectResponse
    {
        $project = $action->execute($request->validated());

        return redirect()->route('admin.projects.edit', $project)
            ->with('success', 'پروژه با موفقیت ایجاد شد.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Project $project
     * @return View
     */
    public function edit(Project $project): View
    {
        $selectedTags = $project->tags
            ->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->title,
            ])
            ->values()
            ->all();

        return view('admin.projects.projects.edit', compact('project', 'selectedTags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProjectRequest $request
     * @param Project $project
     * @param UpdateProjectAction $action
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdateProjectRequest $request, Project $project, UpdateProjectAction $action): RedirectResponse
    {
        $action->execute($project, $request->validated());

        return redirect()
            ->route('admin.projects.edit', $project)
            ->with('success', 'پروژه با موفقیت بروزرسانی شد.');
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
