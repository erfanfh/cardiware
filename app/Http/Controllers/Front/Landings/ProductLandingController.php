<?php

namespace App\Http\Controllers\Front\Landings;

use App\Application\Content\Actions\GetProductLandingPageDataAction;
use Illuminate\View\View;

class ProductLandingController
{
    public function show(string $landing, GetProductLandingPageDataAction $getPageData): View
    {
        return view('front.landings.page', $getPageData->execute($landing));
    }
}
