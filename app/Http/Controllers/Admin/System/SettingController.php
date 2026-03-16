<?php

namespace App\Http\Controllers\Admin\System;

use App\Application\System\Actions\UpdateSettingAction;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Store general settings
     *
     * @param Request $request
     * @param UpdateSettingAction $action
     * @return RedirectResponse
     * @throws BindingResolutionException
     */
    public function storeGeneral(Request $request, UpdateSettingAction $action): RedirectResponse
    {
        $fields = ['title', 'subtitle', 'favicon', 'default_sms_provider'];

        foreach ($fields as $field) {
            $action->execute('general', $field, $request->get($field));
        }

        return redirect()
            ->route('admin.settings.general')
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
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
     * Store header settings
     *
     * @param Request $request
     * @param UpdateSettingAction $action
     * @return RedirectResponse
     * @throws BindingResolutionException
     */
    public function storeHeader(Request $request, UpdateSettingAction $action): RedirectResponse
    {
        $fields = ['logo', 'ajax_search'];

        foreach ($fields as $field) {
            $action->execute('header', $field, $request->get($field));
        }

        return redirect()
            ->route('admin.settings.header')
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
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
