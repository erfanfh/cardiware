<?php

namespace App\Http\Controllers\Front\Project;

use App\Application\Project\Actions\GetProjectDetailAction;
use App\Application\Project\Actions\GetProjectListPageDataAction;
use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        $project = app(GetProjectDetailAction::class)->execute($project);

        return view('front.projects.detail', compact('project'));
    }

    /**
     * Project list
     *
     * @param Request $request
     * @param GetProjectListPageDataAction $action
     * @return View
     */
    public function list(Request $request, GetProjectListPageDataAction $action): View
    {
        $data = $action->execute(
            page: max(1, (int)$request->query('page', 1)),
            perPage: 9,
        );

        return view('front.projects.list', [
            'stats' => $data['stats'],
            'projects' => $data['projects'],
        ]);
    }
}
