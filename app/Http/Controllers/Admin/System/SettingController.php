<?php

namespace App\Http\Controllers\Admin\System;

use App\Application\System\Actions\UpdateSettingAction;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        Gate::authorize('setting.general');

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
        Gate::authorize('setting.general');

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
        Gate::authorize('setting.header');

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
        Gate::authorize('setting.header');

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
        Gate::authorize('setting.footer');

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
        Gate::authorize('setting.footer');

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
        Gate::authorize('setting.home');

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
        Gate::authorize('setting.home');

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
        Gate::authorize('setting.ads');

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
        Gate::authorize('setting.ads');

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
        Gate::authorize('setting.about-us');

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
        Gate::authorize('setting.about-us');

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
        Gate::authorize('setting.sidebar-widgets');

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
        Gate::authorize('setting.sidebar-widgets');

        $this->storeSettings($request, 'sidebarWidgets');

        return redirect()
            ->route('admin.settings.sidebar-widgets')
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    /**
     * General settings page
     *
     * @return View
     */
    public function auth(): View
    {
        Gate::authorize('setting.auth');

        return view('admin.system.settings.auth');
    }

    /**
     * Store home settings
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws BindingResolutionException
     */
    public function storeAuth(Request $request): RedirectResponse
    {
        Gate::authorize('setting.auth');

        $this->storeSettings($request, 'auth');

        return redirect()
            ->route('admin.settings.auth')
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    /**
     * General settings page
     *
     * @return View
     */
    public function projects(): View
    {
        Gate::authorize('setting.projects');

        return view('admin.system.settings.projects');
    }

    /**
     * Store home settings
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws BindingResolutionException
     */
    public function storeProjects(Request $request): RedirectResponse
    {
        Gate::authorize('setting.projects');

        $this->storeSettings($request, 'projects');

        return redirect()
            ->route('admin.settings.projects')
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    /**
     * General settings page
     *
     * @return View
     */
    public function posts(): View
    {
        Gate::authorize('setting.posts');

        return view('admin.system.settings.posts');
    }

    /**
     * Store home settings
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws BindingResolutionException
     */
    public function storePosts(Request $request): RedirectResponse
    {
        Gate::authorize('setting.posts');

        $this->storeSettings($request, 'posts');

        return redirect()
            ->route('admin.settings.posts')
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
