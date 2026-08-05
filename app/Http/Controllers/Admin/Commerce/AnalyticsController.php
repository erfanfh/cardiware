<?php

namespace App\Http\Controllers\Admin\Commerce;

use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AnalyticsController
{
    public function __invoke(): View
    {
        Gate::authorize('analytics.view');

        return view('admin.commerce.analytics.index');
    }
}
