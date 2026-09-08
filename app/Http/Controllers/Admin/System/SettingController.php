<?php

namespace App\Http\Controllers\Admin\System;

use App\Application\Seo\Support\SeoTemplateVariableRegistry;
use App\Application\System\Actions\UpdateSettingAction;
use App\Application\System\Backup\BackupEnvironment;
use App\Application\System\Backup\BackupRunDispatcher;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Organization\Models\TeamMember;
use App\Domain\System\Models\BackupRun;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Loyalty\UpdateLoyaltySettingsRequest;
use App\Http\Requests\Admin\Raffle\UpdateRaffleSettingsRequest;
use App\Http\Requests\Admin\System\UpdateAboutUsSettingsRequest;
use App\Http\Requests\Admin\System\UpdateBackupSettingsRequest;
use App\Http\Requests\Admin\System\UpdateTeamSettingsRequest;
use App\Infrastructure\Services\Setting\Groups\BackupSettings;
use App\Infrastructure\Services\Setting\Groups\LoyaltySettings;
use App\Infrastructure\Services\Setting\Groups\TeamSettings;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class SettingController extends Controller
{
    public function backup(BackupEnvironment $environment): View
    {
        Gate::authorize('setting.backup');

        $backupRuns = BackupRun::query()
            ->latest()
            ->limit(20)
            ->get();
        $environmentChecks = $environment->checks();

        return view('admin.system.settings.backup', compact('backupRuns', 'environmentChecks'));
    }

    public function storeBackup(UpdateBackupSettingsRequest $request): RedirectResponse
    {
        Gate::authorize('setting.backup');

        DB::transaction(function () use ($request): void {
            foreach ($request->validated() as $field => $value) {
                if (!app(UpdateSettingAction::class)->execute('backup', $field, $value)) {
                    throw new RuntimeException('Unable to persist backup settings.');
                }
            }
        });

        return redirect()
            ->route('admin.settings.backup')
            ->with('success', 'تنظیمات بک‌آپ با موفقیت ذخیره شد.');
    }

    public function runBackup(
        Request             $request,
        BackupEnvironment   $environment,
        BackupSettings      $settings,
        BackupRunDispatcher $dispatcher,
    ): RedirectResponse
    {
        Gate::authorize('setting.backup');

        $validated = $request->validate(['type' => ['required', 'in:full,database']]);

        if (!$environment->isReady()) {
            return back()->with('error', 'ابتدا مقادیر اتصال پارس‌پک و رمز آرشیو را در env سرور کامل کنید.');
        }

        if ($validated['type'] === 'database' && !$settings->value('database_enabled')) {
            return back()->with('error', 'بک‌آپ دیتابیس در تنظیمات غیرفعال است.');
        }

        $run = $dispatcher->dispatch($validated['type'], 'manual', $request->user()?->getKey());

        return $run
            ? back()->with('success', 'بک‌آپ در صف اجرا قرار گرفت.')
            : back()->with('error', 'یک بک‌آپ دیگر در حال انتظار یا اجرا است.');
    }

    public function raffle(): View
    {
        Gate::authorize('raffle-campaign.edit');

        return view('admin.system.settings.raffle');
    }

    public function storeRaffle(UpdateRaffleSettingsRequest $request): RedirectResponse
    {
        Gate::authorize('raffle-campaign.edit');

        DB::transaction(function () use ($request): void {
            foreach ($request->validated() as $field => $value) {
                if (!app(UpdateSettingAction::class)->execute('raffle', $field, $value)) {
                    throw new RuntimeException('Unable to persist raffle settings.');
                }
            }
        });

        return redirect()
            ->route('admin.settings.raffle')
            ->with('success', 'تنظیمات قرعه‌کشی با موفقیت ذخیره شد.');
    }

    public function loyalty(LoyaltySettings $settings): View
    {
        Gate::authorize('loyalty-rule.list');

        $selectedRewardOfferId = (int)$settings->get('registration_gift_reward_offer_id', 0);
        $registrationGiftOffers = RewardOffer::query()
            ->where(function ($query) use ($selectedRewardOfferId): void {
                $query->where('is_active', true);

                if ($selectedRewardOfferId > 0) {
                    $query->orWhere('id', $selectedRewardOfferId);
                }
            })
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.system.settings.loyalty', compact('registrationGiftOffers'));
    }

    public function storeLoyalty(UpdateLoyaltySettingsRequest $request): RedirectResponse
    {
        Gate::authorize('loyalty-rule.list');

        DB::transaction(function () use ($request): void {
            foreach ($request->validated() as $field => $value) {
                $updated = app(UpdateSettingAction::class)
                    ->execute('loyalty', $field, $value);

                if (!$updated) {
                    throw new RuntimeException('Unable to persist loyalty settings.');
                }
            }
        });

        return redirect()
            ->route('admin.settings.loyalty')
            ->with('success', 'تنظیمات باشگاه مخاطبان با موفقیت ذخیره شد.');
    }

    /**
     * General settings page
     */
    public function general(): View
    {
        Gate::authorize('setting.general');

        return view('admin.system.settings.general');
    }

    /**
     * Store general settings
     *
     * @throws BindingResolutionException
     */
    public function storeGeneral(Request $request): RedirectResponse
    {
        Gate::authorize('setting.general');

        if ($request->hasAny(['tax_enabled', 'tax_rate_percent'])) {
            $request->validate([
                'tax_enabled' => ['required', 'boolean'],
                'tax_rate_percent' => [
                    'required',
                    'numeric',
                    $request->boolean('tax_enabled') ? 'min:0.01' : 'min:0',
                    'max:100',
                    'decimal:0,2',
                ],
            ], [
                'tax_rate_percent.min' => 'در حالت فعال، درصد مالیات باید بیشتر از صفر باشد.',
                'tax_rate_percent.max' => 'درصد مالیات نمی‌تواند بیشتر از ۱۰۰ باشد.',
            ]);
        }

        if ($request->hasAny(['checkout_terms_enabled', 'checkout_terms_url'])) {
            $request->validate([
                'checkout_terms_enabled' => ['required', 'boolean'],
                'checkout_terms_url' => [
                    $request->boolean('checkout_terms_enabled') ? 'required' : 'nullable',
                    'string',
                    'max:2048',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        if ($value === null || $value === '') {
                            return;
                        }

                        $isSafeRelativeUrl = Str::startsWith($value, '/')
                            && !Str::startsWith($value, '//');
                        $scheme = parse_url($value, PHP_URL_SCHEME);
                        $isSafeAbsoluteUrl = filter_var($value, FILTER_VALIDATE_URL)
                            && in_array(strtolower((string) $scheme), ['http', 'https'], true);

                        if (!$isSafeRelativeUrl && !$isSafeAbsoluteUrl) {
                            $fail('لینک قوانین باید یک آدرس معتبر با http/https یا یک مسیر داخلی مانند /rules باشد.');
                        }
                    },
                ],
            ], [
                'checkout_terms_url.required' => 'در حالت فعال، وارد کردن لینک صفحه قوانین الزامی است.',
            ]);
        }

        $this->storeSettings($request, 'general');
        $activeTab = $request->input('active_tab', 'main');

        return redirect()
            ->route('admin.settings.general', ['tab' => $activeTab])
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    /**
     * General settings page
     */
    public function seo(): View
    {
        Gate::authorize('setting.seo');

        $seoVariableRegistry = app(SeoTemplateVariableRegistry::class);
        $postSeoVariables = $seoVariableRegistry->forContext('post');
        $productSeoVariables = $seoVariableRegistry->forContext('product');
        $pageSeoVariables = $seoVariableRegistry->forContext('page');
        $projectSeoVariables = $seoVariableRegistry->forContext('project');
        $storeSeoVariables = $seoVariableRegistry->forContext('store');
        $taxonomySeoVariables = $seoVariableRegistry->forContext('taxonomy');
        $searchSeoVariables = $seoVariableRegistry->forContext('search');
        $seoVariables = $seoVariableRegistry->forContext('');

        return view('admin.system.settings.seo', compact('postSeoVariables', 'pageSeoVariables', 'projectSeoVariables', 'taxonomySeoVariables', 'searchSeoVariables', 'seoVariables', 'productSeoVariables', 'storeSeoVariables'));
    }

    /**
     * Store seo settings
     *
     * @throws BindingResolutionException
     */
    public function storeSeo(Request $request): RedirectResponse
    {
        Gate::authorize('setting.seo');

        $this->storeSettings($request, 'seo');
        $activeTab = $request->input('active_tab', 'main');

        return redirect()
            ->route('admin.settings.seo', ['tab' => $activeTab])
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    /**
     * Header settings page
     */
    public function header(): View
    {
        Gate::authorize('setting.header');

        return view('admin.system.settings.header');
    }

    /**
     * Store header settings
     *
     * @throws BindingResolutionException
     */
    public function storeHeader(Request $request): RedirectResponse
    {
        Gate::authorize('setting.header');

        $this->storeSettings($request, 'header');

        $activeTab = $request->input('active_tab', 'general');

        return redirect()
            ->route('admin.settings.header', ['tab' => $activeTab])
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    /**
     * Footer settings page
     */
    public function footer(): View
    {
        Gate::authorize('setting.footer');

        return view('admin.system.settings.footer');
    }

    /**
     * Store footer settings
     *
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
     */
    public function home(): View
    {
        Gate::authorize('setting.home');

        return view('admin.system.settings.home');
    }

    /**
     * Store home settings
     *
     * @throws BindingResolutionException
     */
    public function storeHome(Request $request): RedirectResponse
    {
        Gate::authorize('setting.home');

        $this->storeSettings($request, 'home');
        $activeTab = $request->input('active_tab', 'slider');

        return redirect()
            ->route('admin.settings.home', ['tab' => $activeTab])
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    /**
     * About us settings page
     */
    public function aboutUs(): View
    {
        Gate::authorize('setting.about-us');

        return view('admin.system.settings.about-us');
    }

    /**
     * Store about us settings
     *
     * @throws BindingResolutionException
     */
    public function storeAboutUs(UpdateAboutUsSettingsRequest $request): RedirectResponse
    {
        Gate::authorize('setting.about-us');

        DB::transaction(function () use ($request): void {
            foreach ($request->validated() as $field => $value) {
                if (!app(UpdateSettingAction::class)->execute('aboutUs', $field, $value)) {
                    throw new RuntimeException('Unable to persist about us settings.');
                }
            }
        });

        return redirect()
            ->route('admin.settings.about-us')
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    /**
     * Team page settings.
     */
    public function team(TeamSettings $settings): View
    {
        Gate::authorize('setting.team');

        $teamSettings = $settings->values();
        $selectedTeamMemberIds = session()->getOldInput('member_ids', $teamSettings['member_ids'] ?? []);
        $selectedTeamMembers = TeamMember::query()
            ->whereKey($selectedTeamMemberIds)
            ->get()
            ->keyBy('id');

        $selectedTeamMemberOptions = collect($selectedTeamMemberIds)
            ->map(function (mixed $id) use ($selectedTeamMembers): ?array {
                $member = $selectedTeamMembers->get((int) $id);

                if (! $member) {
                    return null;
                }

                return [
                    'value' => $member->getKey(),
                    'label' => $member->full_name.($member->job_title ? " — {$member->job_title}" : ''),
                ];
            })
            ->filter()
            ->values()
            ->all();

        return view('admin.system.settings.team', compact(
            'teamSettings',
            'selectedTeamMemberOptions',
        ));
    }

    public function storeTeam(UpdateTeamSettingsRequest $request): RedirectResponse
    {
        Gate::authorize('setting.team');

        DB::transaction(function () use ($request): void {
            foreach ($request->validated() as $field => $value) {
                if (! app(UpdateSettingAction::class)->execute('team', $field, $value)) {
                    throw new RuntimeException('Unable to persist team page settings.');
                }
            }
        });

        return redirect()
            ->route('admin.settings.team')
            ->with('success', 'تنظیمات صفحه تیم با موفقیت ذخیره شد.');
    }

    /**
     * General settings page
     */
    public function sidebarWidgets(): View
    {
        Gate::authorize('setting.sidebar-widgets');

        return view('admin.system.settings.sidebar-widgets');
    }

    /**
     * Store home settings
     *
     * @throws BindingResolutionException
     */
    public function storeSidebarWidgets(Request $request): RedirectResponse
    {
        Gate::authorize('setting.sidebar-widgets');

        $this->storeSettings($request, 'sidebarWidgets');

        $activeTab = $request->input('active_tab', 'tools');

        return redirect()
            ->route('admin.settings.sidebar-widgets', ['tab' => $activeTab])
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    /**
     * General settings page
     */
    public function auth(): View
    {
        Gate::authorize('setting.auth');

        return view('admin.system.settings.auth');
    }

    /**
     * Store home settings
     *
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
     */
    public function projects(): View
    {
        Gate::authorize('setting.projects');

        return view('admin.system.settings.projects');
    }

    /**
     * Store home settings
     *
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
     */
    public function posts(): View
    {
        Gate::authorize('setting.posts');

        return view('admin.system.settings.posts');
    }

    /**
     * Store home settings
     *
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

    public function products(): View
    {
        Gate::authorize('setting.products');

        return view('admin.system.settings.products');
    }

    public function videos(): View
    {
        Gate::authorize('video.edit');

        return view('admin.system.settings.videos');
    }

    /**
     * @throws BindingResolutionException
     */
    public function storeVideos(Request $request): RedirectResponse
    {
        Gate::authorize('video.edit');

        $this->storeSettings($request, 'videos');

        return redirect()
            ->route('admin.settings.videos')
            ->with('success', 'تنظیمات صفحه ویدیوها با موفقیت ذخیره شد.');
    }

    /**
     * @throws BindingResolutionException
     */
    public function storeProducts(Request $request): RedirectResponse
    {
        Gate::authorize('setting.products');

        $this->storeSettings($request, 'products');

        return redirect()
            ->route('admin.settings.products')
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    public function ticket(): View
    {
        Gate::authorize('setting.ticket');

        return view('admin.system.settings.ticket');
    }

    /**
     * @throws BindingResolutionException
     */
    public function storeTicket(Request $request): RedirectResponse
    {
        Gate::authorize('setting.ticket');

        $this->storeSettings($request, 'ticket');

        return redirect()
            ->route('admin.settings.ticket')
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    /**
     * @return void
     *
     * @throws BindingResolutionException
     */
    private function storeSettings(Request $request, string $group)
    {
        foreach ($request->all() as $field => $value) {
            if (in_array($field, ['_method', '_token'])) {
                continue;
            }

            app(UpdateSettingAction::class)->execute($group, $field, $value);
        }
    }
}
