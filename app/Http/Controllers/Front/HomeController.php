<?php

namespace App\Http\Controllers\Front;

use App\Application\Layout\Actions\GetPageLayoutRenderDataAction;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(GetPageLayoutRenderDataAction $action): View
    {
        $layoutData = $action->execute('home');

        return view('front.home.index', compact('layoutData'));
    }
}
