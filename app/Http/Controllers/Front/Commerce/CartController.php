<?php

namespace App\Http\Controllers\Front\Commerce;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __invoke(): View
    {
        return view('front.commerce.cart.index');
    }
}
