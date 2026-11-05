<?php

namespace App\Http\Controllers\Admin\System;

use App\Application\Seo\Support\SeoTemplateVariableRegistry;
use App\Application\System\Actions\UpdateSettingAction;
use App\Application\Content\Support\ProductLandingContentSchema;
use App\Application\System\Backup\BackupEnvironment;
use App\Application\System\Backup\BackupRunDispatcher;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Organization\Models\TeamMember;
use App\Domain\System\Models\BackupRun;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Loyalty\UpdateLoyaltySettingsRequest;
use App\Http\Requests\Admin\Raffle\UpdateRaffleSettingsRequest;
use App\Http\Requests\Admin\System\UpdateAboutUsSettingsRequest;
use App\Http\Requests\Admin\System\UpdateProductLandingsSettingsRequest;
use App\Http\Requests\Admin\System\UpdateBackupSettingsRequest;
use App\Http\Requests\Admin\System\UpdateCareersSettingsRequest;
use App\Http\Requests\Admin\System\UpdateTeamSettingsRequest;
use App\Http\Requests\Admin\System\UpdateTicketSettingsRequest;
use App\Infrastructure\Services\Setting\Groups\BackupSettings;
use App\Infrastructure\Services\Setting\Groups\LoyaltySettings;
use App\Infrastructure\Services\Setting\Groups\TeamSettings;
use App\Infrastructure\Services\Setting\Groups\ProductLandingsSettings;
use App\Domain\System\Models\Setting;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    public function productLandings(ProductLandingsSettings $settings, ProductLandingContentSchema $schema, string $document = 'ultimate'): View
    {
        Gate::authorize('setting.product-landings');
        abort_unless($settings->isDocument($document), 404);

        $content = $settings->document($document);

        return view('admin.system.settings.product-landings', [
            'document' => $document,
            'content' => $content,
            'contentSchema' => $schema->infer([$settings->documentDefaults($document)]),
            'version' => $settings->version($content),
            'labels' => $settings->documentLabels(),
            'landingSlug' => $settings->slugFor($document),
            'isCustom' => $settings->customProductByKey($document) !== null,
            'bases' => ProductLandingsSettings::labels(),
        ]);
    }

    /** لندینگ محصول جدید (مثلاً PGL6) از روی یکی از لندینگ‌های موجود. */
    public function storeCustomProductLanding(Request $request, ProductLandingsSettings $settings): RedirectResponse
    {
        Gate::authorize('setting.product-landings');

        $data = $request->validate([
            'label' => ['required', 'string', 'min:2', 'max:80'],
            'slug' => ['required', 'string', 'max:60', 'regex:' . ProductLandingsSettings::CUSTOM_SLUG_PATTERN],
            'base' => ['required', 'in:' . implode(',', array_keys(ProductLandingsSettings::BASES))],
        ], [
            'slug.regex' => 'نامک باید با pgl- شروع شود و فقط حروف کوچک انگلیسی، عدد و خط تیره داشته باشد (مثل pgl-6).',
        ], ['label' => 'نام محصول', 'slug' => 'نامک', 'base' => 'پایه']);

        $slug = $data['slug'];
        $taken = in_array($slug, ProductLandingsSettings::BASES, true)
            || array_key_exists($slug, (array) config('product-landings.landings', []))
            || in_array($slug, array_column($settings->customProducts(), 'slug'), true)
            || (\Illuminate\Support\Facades\Schema::hasTable('content_slugs')
                && DB::table('content_slugs')->where('slug', $slug)->exists());
        if ($taken) {
            throw ValidationException::withMessages(['slug' => 'این نامک قبلاً استفاده شده است.']);
        }

        $key = 'p_' . substr(str_replace('-', '_', \Illuminate\Support\Str::after($slug, 'pgl-')), 0, 40);
        if ($settings->customProductByKey($key) !== null) {
            $key = substr($key, 0, 34) . '_' . substr(bin2hex(random_bytes(3)), 0, 5);
        }

        $list = $settings->customProducts();
        $list[] = ['key' => $key, 'slug' => $slug, 'label' => $data['label'], 'base' => $data['base']];
        if (!app(UpdateSettingAction::class)->execute('productLandings', 'custom_products', $list)) {
            throw new RuntimeException('Unable to persist product landing list.');
        }

        return redirect()->route('admin.settings.product-landings', $key)
            ->with('success', 'لندینگ جدید ساخته شد (فعلاً غیرفعال). محتوا را ویرایش کنید و «فعال» را روشن کنید.');
    }

    public function destroyCustomProductLanding(ProductLandingsSettings $settings, string $document): RedirectResponse
    {
        Gate::authorize('setting.product-landings');
        abort_unless($settings->customProductByKey($document) !== null, 404);

        DB::transaction(function () use ($settings, $document): void {
            $list = array_values(array_filter($settings->customProducts(), fn (array $item): bool => $item['key'] !== $document));
            $settings->set('custom_products', $list);
            Setting::query()->where('group', 'product_landings')->where('key', $document)->delete();
            $settings->forgetCache();
        });

        return redirect()->route('admin.settings.product-landings')
            ->with('success', 'لندینگ حذف شد.');
    }

    public function storeProductLandings(UpdateProductLandingsSettingsRequest $request, ProductLandingsSettings $settings, string $document): RedirectResponse
    {
        Gate::authorize('setting.product-landings');
        $content = $request->content();

        try {
            DB::transaction(function () use ($request, $settings, $document, $content): void {
                Setting::query()->where('group', 'product_landings')->where('key', $document)->lockForUpdate()->first();
                $settings->forgetCache();

                if (!hash_equals($settings->version($settings->document($document)), $request->validated('version'))) {
                    throw ValidationException::withMessages([
                        'version' => 'این تنظیمات در صفحه دیگری تغییر کرده است. ابتدا صفحه را دوباره باز کنید و تغییرات جدید را بررسی کنید.',
                    ]);
                }

                app(UpdateSettingAction::class)->executeOrFail('productLandings', $document, $content);
            });
        } finally {
            // Also discard any values read while a failed transaction was open.
            $settings->forgetCache();
        }

        return redirect()->route('admin.settings.product-landings', $document)
            ->with('success', 'تنظیمات لندینگ با موفقیت ذخیره شد.');
    }

    public function careers(): View
    {
        Gate::authorize('setting.careers');

        return view('admin.system.settings.careers');
    }

    public function storeCareers(UpdateCareersSettingsRequest $request): RedirectResponse
    {
        Gate::authorize('setting.careers');

        DB::transaction(function () use ($request): void {
            foreach ($request->validated() as $field => $value) {
                if (! app(UpdateSettingAction::class)->execute('careers', $field, $value)) {
                    throw new RuntimeException('Unable to persist careers settings.');
                }
            }
        });

        return redirect()->route('admin.settings.careers')->with('success', 'تنظیمات صفحه استخدام ذخیره شد.');
    }

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

        $raffleCampaigns = \App\Domain\Raffle\Models\DrawCampaign::query()->latest('id')->limit(50)->pluck('title', 'id');
        $loyaltyLevels = \App\Domain\Loyalty\Models\LoyaltyLevel::query()->orderBy('min_xp')->get();

        return view('admin.system.settings.loyalty', compact('registrationGiftOffers', 'raffleCampaigns', 'loyaltyLevels'));
    }

    public function affiliate(\App\Infrastructure\Services\Setting\Groups\AffiliateSettings $settings): View
    {
        Gate::authorize('affiliate.settings');

        return view('admin.system.settings.affiliate', ['settings' => $settings]);
    }

    public function storeAffiliate(Request $request): RedirectResponse
    {
        Gate::authorize('affiliate.settings');

        $validated = $request->validate([
            'affiliate_enabled' => ['required', 'boolean'],
            'default_commission_type' => ['required', 'in:percent,fixed'],
            'default_commission_value' => [
                'required', 'integer', 'min:0',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if ($request->input('default_commission_type') === 'percent' && (int) $value > 100) {
                        $fail('در حالت درصدی، کمیسیون پیش‌فرض نمی‌تواند بیشتر از ۱۰۰ باشد.');
                    }
                },
            ],
            'default_attribution_days' => ['required', 'integer', 'min:1', 'max:365'],
            'default_cookie_days' => ['required', 'integer', 'min:1', 'max:365'],
            'default_min_withdrawal_amount' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'referral_bonus_enabled' => ['required', 'boolean'],
            'referral_bonus_referrer_points' => ['required', 'integer', 'min:0', 'max:1000000'],
            'referral_bonus_referee_points' => ['required', 'integer', 'min:0', 'max:1000000'],
            'referral_bonus_min_order_amount' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'referral_sale_points_per_1000' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated): void {
            foreach ($validated as $field => $value) {
                if (! app(UpdateSettingAction::class)->execute('affiliate', $field, $value)) {
                    throw new RuntimeException('Unable to persist affiliate settings.');
                }
            }
        });

        return redirect()
            ->route('admin.settings.affiliate')
            ->with('success', 'تنظیمات همکاری در فروش با موفقیت ذخیره شد.');
    }

    public function storeLoyalty(UpdateLoyaltySettingsRequest $request): RedirectResponse
    {
        Gate::authorize('loyalty-rule.list');

        DB::transaction(function () use ($request): void {
            foreach ($request->validated() as $field => $value) {
                if (in_array($field, ['club_raffle_campaign_id', 'elite_min_level_id'], true)) {
                    $value = (int) ($value ?? 0);
                }
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

        $request->validate([
            'system_report_recipients' => [
                'nullable', 'string', 'max:5000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $recipients = preg_split('/[\s,;]+/', trim($value), -1, PREG_SPLIT_NO_EMPTY);
                    if (count($recipients) > 20) {
                        $fail('حداکثر ۲۰ ایمیل برای گزارش روزانه وارد کنید.');
                    }
                    foreach ($recipients as $recipient) {
                        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                            $fail('همه گیرندگان گزارش روزانه باید آدرس ایمیل معتبر داشته باشند.');
                            break;
                        }
                    }
                },
            ],
        ]);

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

        $smsPatternParameters = [
            'login' => ['name' => 'Name', 'time' => 'Time', 'ip' => 'Ip', 'source' => 'Source'],
        ];
        foreach (\App\Domain\Notification\Enums\CustomerSmsType::cases() as $type) {
            $smsPatternParameters[$type->value] = $type->parameters();
        }

        $smsPatternRules = [];
        foreach (['smsir', 'farazsms', 'melipayamak'] as $provider) {
            foreach ($smsPatternParameters as $pattern => $parameters) {
                $smsPatternRules["{$provider}_{$pattern}_pattern"] = ['nullable', 'string', 'max:255'];
                if ($provider !== 'melipayamak') {
                    foreach (array_keys($parameters) as $parameter) {
                        $smsPatternRules["{$provider}_{$pattern}_{$parameter}_parameter"] = ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/'];
                    }
                }
            }
        }
        $request->validate($smsPatternRules);

        foreach (['smsir', 'farazsms'] as $provider) {
            foreach ($smsPatternParameters as $pattern => $parameters) {
                if (! $request->filled("{$provider}_{$pattern}_pattern")) {
                    continue;
                }
                $names = [];
                foreach ($parameters as $parameter => $defaultName) {
                    $field = "{$provider}_{$pattern}_{$parameter}_parameter";
                    $name = $request->input($field) ?: $defaultName;
                    if (in_array($name, $names, true)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            $field => 'نام متغیرهای هر الگوی پیامک باید متفاوت باشند.',
                        ]);
                    }
                    $names[] = $name;
                }
            }
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

        $request->validate([
            'announcement_background_type' => ['sometimes', 'required', 'in:color,image,gradient,pattern'],
            'announcement_gradient_color' => ['nullable', 'hex_color'],
            'announcement_gradient_angle' => ['nullable', 'integer', 'between:0,360'],
            'announcement_animation_duration' => ['nullable', 'integer', 'between:2,120'],
            'announcement_pattern_size' => ['nullable', 'integer', 'between:8,256'],
            'announcement_pattern_image' => ['nullable', 'array:id,path'],
            'announcement_pattern_image.id' => ['nullable', 'integer'],
            'announcement_pattern_image.path' => ['nullable', 'string', 'max:2048'],
        ]);

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

        $request->validate([
            'news_enabled' => ['required', 'boolean'],
            'news_title' => ['nullable', 'string', 'max:100'],
            'news_items' => ['nullable', 'array', 'max:8'],
            'news_items.*.title' => ['required', 'string', 'max:180'],
            'news_items.*.url' => ['nullable', 'string', 'max:2048', 'regex:~^(?:https?://[^\s<>]+|/(?!/)[^\s<>]*)$~i'],
        ]);

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

    public function festival(): View
    {
        Gate::authorize('setting.home');

        return view('admin.system.settings.festival');
    }

    public function storeFestival(Request $request): RedirectResponse
    {
        Gate::authorize('setting.home');

        $this->storeSettings($request, 'festival');

        return redirect()
            ->route('admin.settings.festival')
            ->with('success', 'تنظیمات جشنواره ذخیره شد.');
    }

    public function integrations(): View
    {
        Gate::authorize('setting.home');

        return view('admin.system.settings.integrations');
    }

    public function storeIntegrations(Request $request): RedirectResponse
    {
        Gate::authorize('setting.home');

        $data = $request->validate([
            'torob_pay_enabled' => ['required', 'boolean'],
            'torob_pay_merchant_key' => ['nullable', 'string', 'max:255'],
            'snapp_pay_enabled' => ['required', 'boolean'],
            'snapp_pay_api_key' => ['nullable', 'string', 'max:255'],
            'torob_search_enabled' => ['required', 'boolean'],
            'torob_search_website_id' => ['nullable', 'string', 'max:64'],
        ]);

        foreach ($data as $field => $value) {
            app(UpdateSettingAction::class)->execute('integrations', $field, $value);
        }

        return redirect()
            ->route('admin.settings.integrations')
            ->with('success', 'تنظیمات یکپارچه‌سازی ذخیره شد.');
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

        $request->validate([
            'tools_items' => ['nullable', 'array'],
            'tools_items.*' => ['array'],
            'tools_items.*.description' => ['nullable', 'string', 'max:160'],
        ], [], [
            'tools_items.*.description' => 'توضیح کوتاه ابزار',
        ]);

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
    public function storeTicket(UpdateTicketSettingsRequest $request): RedirectResponse
    {
        Gate::authorize('setting.ticket');

        foreach ($request->validated() as $field => $value) {
            app(UpdateSettingAction::class)->execute('ticket', $field, $value);
        }

        return redirect()
            ->route('admin.settings.ticket')
            ->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    /**
     * Launcher landing settings page.
     */
    public function launcher(): View
    {
        Gate::authorize('setting.launcher');

        return view('admin.system.settings.launcher');
    }

    /**
     * Store launcher landing settings.
     *
     * @throws BindingResolutionException
     */
    public function storeLauncher(Request $request): RedirectResponse
    {
        Gate::authorize('setting.launcher');

        $this->storeSettings($request, 'launcher');

        return redirect()
            ->route('admin.settings.launcher')
            ->with('success', 'تنظیمات لانچر با موفقیت ذخیره شد.');
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
