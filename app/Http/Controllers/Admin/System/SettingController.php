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
     * @return RedirectResponse
     * @throws BindingResolutionException
     */
    public function storeGeneral(Request $request): RedirectResponse
    {
        $this->storeSettings($request, 'general');

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
     * @return RedirectResponse
     * @throws BindingResolutionException
     */
    public function storeHeader(Request $request): RedirectResponse
    {
        $this->storeSettings($request, 'header');

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
     * Store footer settings
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws BindingResolutionException
     */
    public function storeFooter(Request $request): RedirectResponse
    {
        $this->storeSettings($request, 'footer');

        return redirect()
            ->route('admin.settings.footer')
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
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

    /**
     * Store home settings
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws BindingResolutionException
     */
    public function storeHome(Request $request): RedirectResponse
    {
        $this->storeSettings($request, 'home');

        return redirect()
            ->route('admin.settings.home')
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    /**
     * Ads settings page
     *
     * @return View
     */
    public function ads(): View
    {
        return view('admin.system.settings.ads');
    }

    /**
     * Store ads settings
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws BindingResolutionException
     */
    public function storeAds(Request $request): RedirectResponse
    {
        $this->storeSettings($request, 'ads');

        return redirect()
            ->route('admin.settings.ads')
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    /**
     * General settings page
     *
     * @return View
     */
    public function aboutUs()
    {
        return view('admin.system.settings.about-us');
    }

    /**
     * Store home settings
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws BindingResolutionException
     */
    public function storeAboutUs(Request $request): RedirectResponse
    {
        $this->storeSettings($request, 'aboutUs');

        return redirect()
            ->route('admin.settings.about-us')
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    /**
     * General settings page
     *
     * @return View
     */
    public function sidebarWidgets(): View
    {
        return view('admin.system.settings.sidebar-widgets');
    }

    /**
     * Store home settings
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws BindingResolutionException
     */
    public function storeSidebarWidgets(Request $request): RedirectResponse
    {
        $this->storeSettings($request, 'sidebarWidgets');

        return redirect()
            ->route('admin.settings.sidebar-widgets')
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    /**
     * @param Request $request
     * @param string $group
     * @return void
     * @throws BindingResolutionException
     */
    private function storeSettings(Request $request, string $group)
    {
        foreach ($request->all() as $field => $value) {
            if (in_array($field, ['_method', '_token'])) continue;

            app(UpdateSettingAction::class)->execute($group, $field, $value);
        }
    }
}
