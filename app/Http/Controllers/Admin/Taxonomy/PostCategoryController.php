<?php

namespace App\Http\Controllers\Admin\Taxonomy;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PostCategoryController extends Controller
{
    /**
     * Display a listing of categories.
     *
     * @return View
     */
    public function __invoke(): View
    {
        Gate::authorize('category.manage');

        return view('admin.taxonomy.categories.index');
    }
}
