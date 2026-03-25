<?php

namespace App\Http\Controllers\Front\Project;

use App\Domain\Project\Enums\ProjectStatusEnum;
use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Project detail
     *
     * @param Project $project
     * @return View
     */
    public function detail(Project $project): View
    {
        $project = $project->load(['cover.primaryVariant', 'roadmapItems', 'changelogs', 'features']);

        return view('front.projects.detail', compact('project'));
    }

    /**
     * Project list
     *
     * @return View
     */
    public function list(): View
    {
        $projects = Project::paginate(1);

        return view('front.projects.list', compact('projects'));
    }
}
