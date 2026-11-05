@extends('admin.layout')

@section('title', 'تنظیمات باشگاه مخاطبان')

@section('content')
    @php
        $enabled = old('point_conversion_enabled', get_setting('loyalty', 'point_conversion_enabled', false));
        $pointsPerUnit = old('point_conversion_points_per_unit', get_setting('loyalty', 'point_conversion_points_per_unit', 100));
        $walletAmountPerUnit = old('point_conversion_wallet_amount_per_unit', get_setting('loyalty', 'point_conversion_wallet_amount_per_unit', 10000));
        $minimumPoints = old('point_conversion_minimum_points', get_setting('loyalty', 'point_conversion_minimum_points', 500));
        $maximumPoints = old('point_conversion_maximum_points_per_request', get_setting('loyalty', 'point_conversion_maximum_points_per_request', 10000));
        $dailyLimit = old('point_conversion_daily_points_limit', get_setting('loyalty', 'point_conversion_daily_points_limit', 20000));
        $maxCreditPercent = old('max_credit_percent_per_order', get_setting('loyalty', 'max_credit_percent_per_order', 100));
        $streakMilestones = old('streak_milestones', json_encode(get_setting('loyalty', 'streak_milestones', []), JSON_UNESCAPED_UNICODE));
        $expiryMonths = old('points_inactivity_expiry_months', get_setting('loyalty', 'points_inactivity_expiry_months', 12));
        $vipPointsBonusPercent = old('vip_points_bonus_percent', get_setting('loyalty', 'vip_points_bonus_percent', 0));
        $loyaltySettings = app(\App\Infrastructure\Services\Setting\Groups\LoyaltySettings::class);
        $championPrizes = old('champion_prizes', [
            'month' => $loyaltySettings->championPrizes('month'),
            'season' => $loyaltySettings->championPrizes('season'),
            'year' => $loyaltySettings->championPrizes('year'),
        ]);
        $registrationGiftEnabled = old('registration_gift_enabled', get_setting('loyalty', 'registration_gift_enabled', false));
        $registrationGiftOfferId = old('registration_gift_reward_offer_id', get_setting('loyalty', 'registration_gift_reward_offer_id'));
        $registrationGiftOfferOptions = ['' => 'انتخاب پاداش...'] + $registrationGiftOffers
            ->mapWithKeys(fn ($offer) => [
                $offer->getKey() => $offer->title . ' — ' . $offer->type->label()
                    . ($offer->is_active ? '' : ' (غیرفعال)'),
            ])
            ->all();
        $registrationGiftOfferDetails = $registrationGiftOffers
            ->mapWithKeys(function ($offer) {
                $config = $offer->config ?? [];
                $summary = $offer->type->label();

                if ($offer->type->value === 'coupon') {
                    $value = number_format((int) ($config['coupon_value'] ?? 0));
                    $summary = ($config['coupon_type'] ?? null) === 'percentage'
                        ? "{$value} درصد تخفیف"
                        : "{$value} تومان تخفیف";
                    $days = (int) ($config['expires_after_days'] ?? 0);

                    if ($days > 0) {
                        $summary .= " با {$days} روز اعتبار";
                    }
                } elseif ($offer->type->value === 'raffle_ticket') {
                    $summary = number_format((int) ($config['ticket_quantity'] ?? 0)) . ' بلیط قرعه‌کشی';
                }

                return [(string) $offer->getKey() => [
                    'title' => $offer->title,
                    'summary' => $summary,
                    'active' => $offer->isCurrentlyActive(),
                ]];
            })
            ->all();
    @endphp

    <x-admin.form action="{{ route('admin.settings.loyalty.store') }}" method="put">
        <x-admin.page>
            <div
                class="space-y-6"
                x-data="{
                    registrationGiftEnabled: @js((bool) $registrationGiftEnabled),
                    registrationGiftOfferId: @js((string) ($registrationGiftOfferId ?? '')),
                    registrationGiftOffers: @js($registrationGiftOfferDetails),
                    points: Number(@js($pointsPerUnit)) || 1,
                    wallet: Number(@js($walletAmountPerUnit)) || 1,
                    minimum: Number(@js($minimumPoints)) || 1,
                    format(value) {
                        return new Intl.NumberFormat('fa-IR').format(Number(value) || 0);
                    },
                    preview() {
                        return Math.floor(this.minimum / Math.max(1, this.points)) * this.wallet;
                    },
                    selectedGift() {
                        return this.registrationGiftOffers[String(this.registrationGiftOfferId)] || null;
                    }
                }"
            >
                <x-admin.form.validation-errors/>

                <x-admin.metabox title="هدیه خوش‌آمدگویی پس از ثبت‌نام" :collapsible="false">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-medium text-gray-500">فعال‌سازی هدیه</label>
                            <x-admin.field :error="$errors->first('registration_gift_enabled')">
                                <x-admin.form.switch
                                    name="registration_gift_enabled"
                                    :checked="$registrationGiftEnabled"
                                    x-model="registrationGiftEnabled"
                                />
                            </x-admin.field>
                        </div>

                        <x-admin.field
                            label="پاداش هدیه ثبت‌نام"
                            hint="پاداش از لیست پاداش‌های باشگاه انتخاب می‌شود و امتیازی از کاربر کم نمی‌شود."
                            :error="$errors->first('registration_gift_reward_offer_id')"
                        >
                            <x-admin.form.select
                                name="registration_gift_reward_offer_id"
                                :options="$registrationGiftOfferOptions"
                                :value="$registrationGiftOfferId"
                                x-model="registrationGiftOfferId"
                            />
                        </x-admin.field>

                        <div
                            x-show="registrationGiftEnabled && selectedGift()"
                            x-cloak
                            class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 lg:col-span-2"
                        >
                            <div class="text-xs font-black text-emerald-800" x-text="selectedGift()?.title"></div>
                            <div class="mt-1 text-xs leading-6 text-emerald-700" x-text="selectedGift()?.summary"></div>
                            <div
                                x-show="selectedGift() && !selectedGift().active"
                                class="mt-2 text-xs font-bold text-rose-600"
                            >
                                این پاداش در بازه فعلی قابل صدور نیست؛ تا زمان فعال شدن آن هدیه‌ای صادر نمی‌شود.
                            </div>
                        </div>

                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs leading-7 text-slate-600 lg:col-span-2">
                            صدور فقط برای ثبت‌نام‌های جدید پس از فعال‌سازی انجام می‌شود و برای هر کاربر دقیقاً یک‌بار
                            است.
                            درصد، مبلغ، مدت اعتبار و سایر جزئیات از خود پاداش انتخاب‌شده خوانده می‌شود.
                            @can('loyalty-reward.create')
                                <a href="{{ route('admin.reward-offers.create') }}" class="font-black text-brand-600">
                                    ساخت پاداش جدید
                                </a>
                            @endcan
                        </div>
                    </div>
                </x-admin.metabox>

                <div
                    class="rounded-2xl border border-amber-200 bg-gradient-to-l from-amber-50 to-orange-50 p-4 text-sm leading-7 text-amber-900">
                    اعتبار حاصل از تبدیل، <strong>غیرقابل برداشت</strong> است و فقط برای خرید و پرداخت داخل سایت قابل
                    استفاده خواهد بود.
                    همه تبدیل‌ها هم‌زمان در دفتر امتیاز و دفتر کیف پول ثبت می‌شوند.
                </div>

                <x-admin.metabox title="تبدیل امتیاز به موجودی کیف پول" :collapsible="false">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-medium text-gray-500">فعال‌سازی قابلیت</label>
                            <x-admin.field
                                :error="$errors->first('point_conversion_enabled')"
                            >
                                <x-admin.form.switch
                                    name="point_conversion_enabled"
                                    :checked="$enabled"
                                />
                            </x-admin.field>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-bold text-slate-500">نمونه نرخ فعلی</div>
                            <div class="mt-2 text-sm font-black text-slate-900">
                                <span x-text="format(points)"></span> امتیاز
                                <span class="mx-1 text-slate-400">=</span>
                                <span x-text="format(wallet)"></span> تومان
                            </div>
                            <div class="mt-2 text-[11px] text-slate-500">
                                حداقل تبدیل، <span class="font-bold" x-text="format(preview())"></span> تومان اعتبار
                                ایجاد می‌کند.
                            </div>
                        </div>

                        <x-admin.field
                            label="امتیاز هر واحد تبدیل"
                            hint="مقدار درخواستی کاربر باید مضربی از این عدد باشد."
                            :error="$errors->first('point_conversion_points_per_unit')"
                            required
                        >
                            <x-admin.form.input
                                type="number"
                                min="1"
                                step="1"
                                name="point_conversion_points_per_unit"
                                :value="$pointsPerUnit"
                                x-model.number="points"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="اعتبار کیف پول برای هر واحد (تومان)"
                            hint="این مبلغ به موجودی غیرقابل برداشت کیف پول اضافه می‌شود."
                            :error="$errors->first('point_conversion_wallet_amount_per_unit')"
                            required
                        >
                            <x-admin.form.input
                                type="number"
                                min="1"
                                step="1"
                                name="point_conversion_wallet_amount_per_unit"
                                :value="$walletAmountPerUnit"
                                x-model.number="wallet"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="حداقل امتیاز هر تبدیل"
                            hint="باید مضربی از واحد تبدیل باشد."
                            :error="$errors->first('point_conversion_minimum_points')"
                            required
                        >
                            <x-admin.form.input
                                type="number"
                                min="1"
                                step="1"
                                name="point_conversion_minimum_points"
                                :value="$minimumPoints"
                                x-model.number="minimum"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="حداکثر امتیاز در هر درخواست"
                            hint="برای نامحدود بودن مقدار صفر وارد کنید."
                            :error="$errors->first('point_conversion_maximum_points_per_request')"
                            required
                        >
                            <x-admin.form.input
                                type="number"
                                min="0"
                                step="1"
                                name="point_conversion_maximum_points_per_request"
                                :value="$maximumPoints"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="سقف امتیاز قابل تبدیل در روز"
                            hint="برای نامحدود بودن مقدار صفر وارد کنید."
                            :error="$errors->first('point_conversion_daily_points_limit')"
                            required
                        >
                            <x-admin.form.input
                                type="number"
                                min="0"
                                step="1"
                                name="point_conversion_daily_points_limit"
                                :value="$dailyLimit"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="سقف اعتبار باشگاه در هر سفارش (درصد)"
                            hint="حداکثر سهم اعتبار باشگاه (غیرقابل برداشت) از پرداخت هر سفارش. مثال: ۲۰ یعنی حداکثر ۲۰٪ مبلغ سفارش از اعتبار باشگاه قابل پرداخت است و مابقی باید از درگاه پرداخت شود. صفر یا ۱۰۰ = بدون محدودیت."
                            :error="$errors->first('max_credit_percent_per_order')"
                            required
                        >
                            <x-admin.form.input
                                type="number"
                                min="0"
                                max="100"
                                step="1"
                                name="max_credit_percent_per_order"
                                :value="$maxCreditPercent"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="مزیت اشتراک ویژه (VIP) در باشگاه">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-admin.field
                            label="پاداش امتیاز کاربران VIP (درصد)"
                            hint="کاربر دارای اشتراک ویژه فعال از هر امتیاز قوانین کسب امتیاز (خرید، ورود روزانه، دیدگاه، زمان حضور و ...) این درصد بیشتر می‌گیرد. چون سطح باشگاه هم با همین امتیاز بالا می‌رود، ارتقای سطحش سریع‌تر است. مثال: ۵۰ یعنی ۱٫۵ برابر. صفر = خاموش. حداکثر ۵۰۰."
                            :error="$errors->first('vip_points_bonus_percent')"
                            required
                        >
                            <x-admin.form.input
                                type="number"
                                min="0"
                                max="500"
                                step="1"
                                name="vip_points_bonus_percent"
                                :value="$vipPointsBonusPercent"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="برترین‌های ماه، فصل و سال">
                    <p class="mb-4 text-xs leading-7 text-slate-500">
                        نفرات ۱ تا ۳ هر دوره (بر اساس XP همان دوره) به‌صورت خودکار این جوایز را می‌گیرند. امتیاز جایزه قابل خرج است و XP نمی‌دهد؛
                        کد تخفیف شخصی و یک‌بارمصرف است؛ کدهای قرعه‌کشی در «قرعه‌کشی باشگاه» ثبت می‌شوند.
                    </p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                            <tr class="border-b border-slate-200 text-right text-slate-400">
                                <th class="px-2 py-2">دوره / رتبه</th>
                                <th class="px-2 py-2">امتیاز</th>
                                <th class="px-2 py-2">درصد کد تخفیف</th>
                                <th class="px-2 py-2">سقف تخفیف (تومان)</th>
                                <th class="px-2 py-2">اعتبار کد (روز)</th>
                                <th class="px-2 py-2">کد قرعه‌کشی</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach(['month' => 'ماه', 'season' => 'فصل', 'year' => 'سال'] as $periodKey => $periodName)
                                @foreach(range(0, 2) as $rankIndex)
                                    <tr class="border-b border-slate-100">
                                        <td class="px-2 py-2 font-bold">{{ $periodName }} — نفر {{ ['اول', 'دوم', 'سوم'][$rankIndex] }}</td>
                                        @foreach(['points', 'coupon_percent', 'coupon_max_amount', 'coupon_days', 'raffle_tickets'] as $field)
                                            <td class="px-2 py-1.5">
                                                <input type="number" min="0" dir="ltr"
                                                       name="champion_prizes[{{ $periodKey }}][{{ $rankIndex }}][{{ $field }}]"
                                                       value="{{ $championPrizes[$periodKey][$rankIndex][$field] ?? 0 }}"
                                                       class="h-9 w-24 rounded-lg border border-slate-200 px-2 text-xs">
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @error('champion_prizes')<p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>@enderror

                    <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-admin.field label="جایزه ویژه نفر اول سال" hint="مثلاً «کنسول PS5». مدیر آن را دستی تحویل می‌دهد و در «برترین‌ها و جوایز» ثبت می‌کند.">
                            <x-admin.form.input name="champion_year_grand_prize" :value="old('champion_year_grand_prize', get_setting('loyalty', 'champion_year_grand_prize', 'کنسول بازی'))"/>
                        </x-admin.field>
                        <x-admin.field label="حد نصاب سالانه: حداقل XP سال">
                            <x-admin.form.input type="number" min="0" name="champion_year_min_xp" :value="old('champion_year_min_xp', get_setting('loyalty', 'champion_year_min_xp', 20000))"/>
                        </x-admin.field>
                        <x-admin.field label="حد نصاب سالانه: مأموریت‌های کامل‌شده">
                            <x-admin.form.input type="number" min="0" name="champion_year_min_missions" :value="old('champion_year_min_missions', get_setting('loyalty', 'champion_year_min_missions', 20))"/>
                        </x-admin.field>
                        <x-admin.field label="حد نصاب سالانه: تعداد خرید">
                            <x-admin.form.input type="number" min="0" name="champion_year_min_orders" :value="old('champion_year_min_orders', get_setting('loyalty', 'champion_year_min_orders', 3))"/>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="قرعه‌کشی باشگاه و کانون نخبگان">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <x-admin.field label="قرعه‌کشی باشگاه" hint="کدهای قرعه‌کشی برترین‌ها و خریدها در این قرعه‌کشی ثبت می‌شوند (باید در حال دریافت کد باشد). هر ماه قرعه‌کشی جدید را اینجا انتخاب کنید.">
                            <x-admin.form.select name="club_raffle_campaign_id"
                                                 :options="['' => 'بدون قرعه‌کشی'] + $raffleCampaigns->all()"
                                                 :value="old('club_raffle_campaign_id', get_setting('loyalty', 'club_raffle_campaign_id'))"/>
                        </x-admin.field>
                        <x-admin.field label="یک کد قرعه‌کشی به ازای هر … تومان خرید" hint="مثلاً ۲۰۰۰۰۰ یعنی خرید ۱ میلیونی = ۵ کد. با مرجوعی باطل می‌شود. صفر = خاموش.">
                            <x-admin.form.input type="number" min="0" name="purchase_raffle_toman_per_ticket" :value="old('purchase_raffle_toman_per_ticket', get_setting('loyalty', 'purchase_raffle_toman_per_ticket', 0))"/>
                        </x-admin.field>
                        <x-admin.field label="کانون نخبگان: از سطح" hint="اعضای این سطح به بالا در نظرسنجی‌های «فقط کانون نخبگان» رأی می‌دهند و نشان کانون می‌گیرند.">
                            <x-admin.form.select name="elite_min_level_id"
                                                 :options="['' => 'غیرفعال'] + $loyaltyLevels->mapWithKeys(fn ($level) => [$level->id => $level->title . ' (از ' . number_format($level->min_xp) . ' XP)'])->all()"
                                                 :value="old('elite_min_level_id', get_setting('loyalty', 'elite_min_level_id'))"/>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="زنجیره ورود روزانه و انقضای امتیاز">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-admin.field
                            label="مایل‌استون‌های زنجیره ورود (JSON)"
                            hint='ساختار: [{"days":7,"points":50,"repeat":true},{"days":30,"points":500,"repeat":true},{"days":60,"points":0,"vip_days":7},{"days":90,"points":0,"vip_days":30,"repeat":true}] — points امتیاز و vip_days روزهای اشتراک VIP هدیه است. repeat:true یعنی در هر مضرب days تکرار می‌شود (۹۰، ۱۸۰، ...)، وگرنه فقط یک‌بار در هر زنجیره. با قطع زنجیره، شمارش از روز اول شروع می‌شود.'
                            :error="$errors->first('streak_milestones')"
                        >
                            <x-admin.form.input
                                type="text"
                                name="streak_milestones"
                                :value="$streakMilestones"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="انقضای امتیاز بعد از عدم فعالیت (ماه)"
                            hint="اگر کاربر این مدت هیچ گردش امتیازی نداشته باشد، کل موجودی‌اش با ردیف انقضا صفر می‌شود. صفر = انقضا خاموش."
                            :error="$errors->first('points_inactivity_expiry_months')"
                        >
                            <x-admin.form.input
                                type="number"
                                min="0"
                                step="1"
                                name="points_inactivity_expiry_months"
                                :value="$expiryMonths"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>
            </div>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
