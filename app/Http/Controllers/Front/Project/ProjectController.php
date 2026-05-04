<?php

namespace App\Http\Controllers\Front\Project;

use App\Application\Project\Actions\GetProjectDetailAction;
use App\Application\Project\Actions\GetProjectListPageDataAction;
use App\Domain\Project\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
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
     * @param int|null $page
     * @return View|RedirectResponse
     */
    public function list(
        Request                      $request,
        GetProjectListPageDataAction $action,
        ?int                         $page = null,
    ): View|RedirectResponse
    {
        $page = max(1, (int)($page ?? $request->route('page', 1)));

        if ($page === 1 && $request->routeIs('projects.list.page')) {
            return redirect()->to(front_route('projects.list'), 301);
        }

        $data = $action->execute(
            page: $page,
            perPage: 9,
        );

        $projects = $data['projects'];

        if ($page > 1 && $projects->lastPage() > 0 && $page > $projects->lastPage()) {
            abort(404);
        }

        $request->attributes->set('seo_paginator', $projects);
        $request->attributes->set('seo_pagination_base_url', front_route('projects.list'));

        return view('front.projects.list', [
            'stats' => $data['stats'],
            'projects' => $projects,
        ]);
    }
}
