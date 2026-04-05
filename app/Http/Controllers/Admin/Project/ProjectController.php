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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
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
        Gate::authorize('project.list');

        $projects = $projectListing
            ->handle(Project::query(), $request->validated())
            ->paginate($projectListing->perPage())
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

        $bulkActionOptions = DefaultBulkActions::set()->toOptions();

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
        Gate::authorize('project.create');

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
        Gate::authorize('project.create');

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
        Gate::authorize('project.edit');

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
        Gate::authorize('project.edit');

        $action->execute($project, $request->validated());

        return redirect()
            ->route('admin.projects.edit', $project)
            ->with('success', 'پروژه با موفقیت بروزرسانی شد.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        Gate::authorize('project.delete');

        $project->delete();

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'پروژه با موفقیت حذف شد.');
    }
}
