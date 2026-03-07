<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * General settings page
     *
     * @return View
     */
    public function general(): View
    {
        return view('admin.system.settings.general');
    }

    /**
     * Header settings page
     *
     * @return View
     */
    public function header(): View
    {
        return view('admin.system.settings.header');
    }

    /**
     * Footer settings page
     *
     * @return View
     */
    public function footer(): View
    {
        return view('admin.system.settings.footer');
    }

    /**
     * Home settings page
     *
     * @return View
     */
    public function home(): View
    {
        return view('admin.system.settings.home');
    }
}
