<?php

namespace App\Http\Controllers\Admin\Seo;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RedirectController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('redirect.manage');

        return view('admin.seo.redirects.index');
    }
}
