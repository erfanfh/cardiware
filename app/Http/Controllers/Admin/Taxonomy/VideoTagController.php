<?php

namespace App\Http\Controllers\Admin\Taxonomy;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VideoTagController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('video.tag');
        return view('admin.video.taxonomy.tags');
    }
}
