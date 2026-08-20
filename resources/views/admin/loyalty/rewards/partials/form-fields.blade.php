@php
    use App\Domain\Loyalty\Enums\RewardTypeEnum;
    use App\Domain\Promotion\Enums\CouponTypeEnum;

    $rewardOffer = $rewardOffer ?? null;
    $selectedType = old('type', $rewardOffer?->type?->value ?? RewardTypeEnum::COUPON->value);
    $defaultConfig = $defaultConfigs[$selectedType] ?? [];
    $config = old('config', $rewardOffer?->config ?? $defaultConfig);
    $couponType = $config['coupon_type'] ?? CouponTypeEnum::FIXED_AMOUNT->value;
    $isActive = old('is_active', $rewardOffer?->is_active ?? true);
    $targetId = old('target_id', $rewardOffer?->target_id);
    $hasRedemptions = (int)($rewardOffer?->redemptions_count ?? 0) > 0;
@endphp

<x-admin.page>
    <x-admin.form.validation-errors class="mb-6"/>

    <div
        x-data="{
            rewardType: @js($selectedType),
            couponType: @js($couponType),
            defaults: @js($defaultConfigs),
            onTypeChange() {
                const defaults = this.defaults[this.rewardType] || {};
                this.$nextTick(() => {
                    if (this.rewardType === 'coupon' && defaults.coupon_type) {
                        this.couponType = defaults.coupon_type;
                    }
                });
            }
        }"
        class="space-y-5"
    >
        <x-admin.metabox title="اطلاعات اصلی پاداش">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-admin.field required label="عنوان پاداش">
                    <x-admin.form.input name="title" :value="old('title', $rewardOffer?->title)"/>
                </x-admin.field>

                <x-admin.field required label="کد داخلی">
                    <x-admin.form.input name="code" dir="ltr" :value="old('code', $rewardOffer?->code)"
                                        placeholder="شناسه یکتا؛ فقط حروف انگلیسی کوچک، عدد، خط تیره، زیرخط و نقطه"/>
                </x-admin.field>

                <x-admin.field required label="نوع پاداش"
                               :hint="$hasRedemptions ? 'پس از اولین دریافت، نوع پاداش قابل تغییر نیست.' : null">
                    <x-admin.form.select
                        name="type"
                        :options="$types"
                        :value="$selectedType"
                        x-model="rewardType"
                        x-on:change="onTypeChange()"
                        :disabled="$hasRedemptions"
                    />
                    @if($hasRedemptions)
                        <input type="hidden" name="type" value="{{ $selectedType }}">
                    @endif
                </x-admin.field>

                <x-admin.field required label="هزینه دریافت (امتیاز)">
                    <x-admin.form.input name="points_cost" type="number" min="1" dir="ltr"
                                        :value="old('points_cost', $rewardOffer?->points_cost ?? 500)"/>
                </x-admin.field>

                <x-admin.field required label="ترتیب نمایش">
                    <x-admin.form.input name="sort_order" type="number" min="0" max="65535" dir="ltr"
                                        :value="old('sort_order', $rewardOffer?->sort_order ?? 0)"/>
                </x-admin.field>

                <div class="md:col-span-2">
                    <x-admin.field label="توضیحات">
                        <x-admin.form.textarea name="description" rows="4"
                                               :value="old('description', $rewardOffer?->description)"/>
                    </x-admin.field>
                </div>

                <div class="md:col-span-2">
                    <label
                        class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div>
                            <div class="text-sm font-bold text-slate-800">فعال بودن پاداش</div>
                            <p class="mt-1 text-xs leading-6 text-slate-500">
                                فعال بودن پاداش به تنهایی کافی نیست؛ برای
                                بلیط قرعه کشی، خود کمپین هم باید در وضعیت دریافت بلیط باشد.
                            </p>
                        </div>
                        <x-admin.form.switch name="is_active" :checked="$isActive"/>
                    </label>
                </div>
            </div>
        </x-admin.metabox>

        <div x-show="rewardType === 'coupon'" x-cloak>
            <x-admin.metabox title="تنظیمات کد تخفیف">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-admin.field required label="نوع تخفیف">
                        <x-admin.form.select name="config[coupon_type]" :options="$couponTypes"
                                             :value="old('config.coupon_type', $config['coupon_type'] ?? 'fixed_amount')"
                                             x-model="couponType"/>
                    </x-admin.field>

                    <x-admin.field required label="مقدار تخفیف" hint="در حالت مبلغ ثابت، مقدار بر حسب تومان است.">
                        <x-admin.form.input name="config[coupon_value]" type="number" min="1" dir="ltr"
                                            :value="old('config.coupon_value', $config['coupon_value'] ?? 100000)"/>
                    </x-admin.field>

                    <div x-show="couponType === 'percentage'" x-cloak>
                        <x-admin.field label="حداکثر مبلغ تخفیف">
                            <x-admin.form.input name="config[max_discount_amount]" type="number" min="1" dir="ltr"
                                                :value="old('config.max_discount_amount', $config['max_discount_amount'] ?? '')"/>
                        </x-admin.field>
                    </div>

                    <x-admin.field required label="حداقل مبلغ سفارش">
                        <x-admin.form.input name="config[minimum_order_amount]" type="number" min="0" dir="ltr"
                                            :value="old('config.minimum_order_amount', $config['minimum_order_amount'] ?? 0)"/>
                    </x-admin.field>

                    <x-admin.field label="مدت اعتبار پس از دریافت (روز)">
                        <x-admin.form.input name="config[expires_after_days]" type="number" min="1" max="3650" dir="ltr"
                                            :value="old('config.expires_after_days', $config['expires_after_days'] ?? 7)"/>
                    </x-admin.field>

                    <div class="md:col-span-2">
                        <label
                            class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div>
                                <div class="text-sm font-bold text-slate-800">اعمال تخفیف روی خدمات افزوده</div>
                            </div>
                            <x-admin.form.switch name="config[applies_to_addons]"
                                                 :checked="old('config.applies_to_addons', $config['applies_to_addons'] ?? false)"/>
                        </label>
                    </div>
                </div>
            </x-admin.metabox>
        </div>

        <div x-show="rewardType === 'raffle_ticket'" x-cloak>
            <x-admin.metabox title="تنظیمات بلیط قرعه کشی">
                <div
                    class="mb-5 rounded-2xl border border-brand-100 bg-brand-50/70 px-4 py-3 text-xs leading-7 text-slate-700">
                    Loyalty فقط هزینه امتیازی را مدیریت می کند. صدور واقعی بلیط، ظرفیت، سقف هر کاربر و کدهای Ticket
                    تماما توسط Raffle System کنترل می شوند.
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-admin.field required label="قرعه کشی هدف"
                                   :hint="$hasRedemptions ? 'پس از اولین دریافت، قرعه کشی هدف قابل تغییر نیست.' : 'Campaign می تواند هنوز شروع نشده باشد؛ دریافت پاداش فقط زمانی ممکن است که Campaign بلیط بپذیرد.'">
                        <x-admin.form.select
                            name="target_id"
                            :options="['' => 'انتخاب قرعه کشی'] + $raffleCampaignOptions"
                            :value="$targetId"
                            :disabled="$hasRedemptions"
                        />
                        @if($hasRedemptions)
                            <input type="hidden" name="target_id" value="{{ $targetId }}">
                        @endif
                    </x-admin.field>

                    <x-admin.field required label="تعداد بلیط در هر دریافت">
                        <x-admin.form.input name="config[ticket_quantity]" type="number" min="1" max="1000" dir="ltr"
                                            :value="old('config.ticket_quantity', $config['ticket_quantity'] ?? 1)"/>
                    </x-admin.field>
                </div>
            </x-admin.metabox>
        </div>

        <x-admin.metabox title="بازه و محدودیت دریافت">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-admin.field label="زمان شروع">
                    <x-admin.form.date-picker name="starts_at"
                                              :value="old('starts_at', $rewardOffer?->starts_at ? jalali_date($rewardOffer->starts_at) : '')"/>
                </x-admin.field>

                <x-admin.field label="زمان پایان">
                    <x-admin.form.date-picker name="ends_at"
                                              :value="old('ends_at', $rewardOffer?->ends_at ? jalali_date($rewardOffer->ends_at) : '')"/>
                </x-admin.field>

                <x-admin.field label="ظرفیت کل دریافت">
                    <x-admin.form.input name="redemption_limit" type="number" min="1" dir="ltr"
                                        :value="old('redemption_limit', $rewardOffer?->redemption_limit)"/>
                </x-admin.field>

                <x-admin.field label="سقف دریافت هر کاربر">
                    <x-admin.form.input name="redemption_limit_per_user" type="number" min="1" dir="ltr"
                                        :value="old('redemption_limit_per_user', $rewardOffer?->redemption_limit_per_user ?? 1)"/>
                </x-admin.field>
            </div>
        </x-admin.metabox>
    </div>
</x-admin.page>
