<?php

namespace App\Http\Controllers\Admin\Taxonomy;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class TagController extends Controller
{
    /**
     * Display a listing of tags.
     *
     * @return View
     */
    public function __invoke(): View
    {
        return view('admin.taxonomy.tags.index');
    }
}
