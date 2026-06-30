<?php

namespace App\Http\Controllers\Front\System;

use Illuminate\View\View;

class NamadController
{
    public function __invoke(): View
    {
        return view('front.system.namad');
    }
}
