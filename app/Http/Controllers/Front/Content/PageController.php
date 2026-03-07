<?php

namespace App\Http\Controllers\Front\Content;

use App\Domain\Content\Models\Page;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Page detail
     *
     * @param Page $page
     * @return View
     */
    public function detail(Page $page): View
    {
        return view('front.pages.detail', compact('page'));
    }
}
