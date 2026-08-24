<?php

namespace App\Http\Controllers\Admin\Taxonomy;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VideoCategoryController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('video.category');
        return view('admin.video.taxonomy.categories');
    }
}
