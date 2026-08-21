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
                    </div>
                </x-admin.metabox>
            </div>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
