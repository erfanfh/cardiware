<?php

namespace App\Http\Controllers\Admin\System;

use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SystemReportController
{
    public function __invoke(): View
    {
        Gate::authorize('report.view');

        return view('admin.system.reports.index');
    }
}
