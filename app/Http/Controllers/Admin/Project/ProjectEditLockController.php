<?php

namespace App\Http\Controllers\Admin\Project;

use App\Application\Project\Support\ProjectEditLock;
use App\Domain\Project\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProjectEditLockController
{
    public function renew(Request $request, Project $project, ProjectEditLock $locks): Response
    {
        Gate::authorize('project.edit');
        $data = $request->validate(['edit_lock_token' => ['required', 'string', 'size:64']]);
        $locks->renew($project, $request->user(), $data['edit_lock_token']);

        return response()->noContent();
    }

    public function release(Request $request, Project $project, ProjectEditLock $locks): Response
    {
        Gate::authorize('project.edit');
        $data = $request->validate(['edit_lock_token' => ['required', 'string', 'size:64']]);
        $locks->release($project, $request->user(), $data['edit_lock_token']);

        return response()->noContent();
    }
}
