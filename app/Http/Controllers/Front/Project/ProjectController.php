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
        $stats =  Project::query()
            ->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN progress_percent = 0 THEN 1 ELSE 0 END) as not_started,
            SUM(CASE WHEN progress_percent BETWEEN 1 AND 99 THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN progress_percent = 100 THEN 1 ELSE 0 END) as completed
            ")
            ->first();

        return view('front.projects.list', compact('stats'));
    }
}
