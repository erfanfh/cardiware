<?php

namespace App\Http\Controllers\Admin\Project;

use App\Application\Project\Actions\CreateProjectAction;
use App\Application\Project\Actions\UpdateProjectAction;
use App\Application\Project\Listings\ProjectListing;
use App\Domain\Project\Enums\ProjectStatusEnum;
use App\Domain\Project\Models\Project;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Project\IndexProjectRequest;
use App\Http\Requests\Admin\Project\UpdateProjectRequest;
use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
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
     * @param IndexProjectRequest $request
     * @param ProjectListing $projectListing
     * @return View
     */
    public function index(IndexProjectRequest $request, ProjectListing $projectListing): View
    {
        $projects = $projectListing
            ->handle(Project::query(), $request->validated())
            ->paginate(20)
            ->withQueryString();

        $statuses = collect(ProjectStatusEnum::cases())
            ->mapWithKeys(fn(ProjectStatusEnum $status) => [
                $status->value => $status->label(),
            ])
            ->all();

        $hasActiveFilters = $projectListing->hasActiveFilters($request->validated());

        $selectedTagIds = collect(request('tag_ids', []))
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->values();

        $selectedTagOptions = TaxonomyTerm::query()
            ->whereIn('id', $selectedTagIds)
            ->get(['id', 'title'])
            ->map(fn($tag) => [
                'value' => (string)$tag->id,
                'label' => $tag->title,
            ])
            ->values()
            ->all();

        $bulkActionOptions = DefaultBulkActions::toOptions();

        return view('admin.projects.projects.index', compact(
            'projects',
            'statuses',
            'hasActiveFilters',
            'selectedTagOptions',
            'bulkActionOptions',
        ));
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
