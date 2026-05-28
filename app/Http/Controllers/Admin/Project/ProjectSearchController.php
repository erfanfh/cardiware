<?php

namespace App\Http\Controllers\Admin\Project;

use App\Domain\Project\Models\Project;
use App\Http\Controllers\Admin\AdminSearchController;

class ProjectSearchController extends AdminSearchController
{
    protected function modelClass(): string
    {
        return Project::class;
    }

    protected function permissionName(): string
    {
        return 'project.list';
    }
}
