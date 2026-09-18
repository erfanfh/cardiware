<?php

namespace App\Http\Controllers\Admin\Media;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View
    {
        Gate::authorize('media.list');

        return view('admin.media.index');
    }
}
