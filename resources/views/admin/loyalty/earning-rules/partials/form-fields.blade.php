@php
    use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;

    $earningRule = $earningRule ?? null;
    $selectedType = old(
        'type',
        $earningRule?->type?->value ?? EarningRuleTypeEnum::REGISTRATION->value
    );

    $initialConfig = old(
        'config',
        $earningRule?->config ?? ($ruleDefaults[$selectedType] ?? [])
    );

    $isActive = old('is_active', $earningRule?->is_active ?? true);
@endphp

<x-admin.page>
    <x-admin.form.validation-errors class="mb-6"/>

    <div
        x-data="{
            ruleType: @js($selectedType),
            defaults: @js($ruleDefaults),
            config: @js($initialConfig),
            changeType(type) {
                this.ruleType = type;
                this.config = { ...(this.defaults[type] || {}) };
            }
        }"
        class="space-y-5"
    >
        <x-admin.metabox title="اطلاعات اصلی قانون">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-admin.field required label="عنوان قانون">
                    <x-admin.form.input
                        name="title"
                        :value="old('title', $earningRule?->title)"
                    />
                </x-admin.field>

                <x-admin.field required label="کد قانون">
                    <x-admin.form.input
                        name="code"
                        dir="ltr"
                        :value="old('code', $earningRule?->code)"
                        placeholder="شناسه داخلی و یکتا؛ فقط حروف انگلیسی کوچک، عدد، خط تیره، زیرخط و نقطه"
                    />
                </x-admin.field>

                <x-admin.field required label="نوع قانون">
                    <x-admin.form.select
                        name="type"
                        :options="$types"
                        :value="$selectedType"
                        x-on:change="changeType($event.target.value)"
                    />
                </x-admin.field>

                <x-admin.field
                    required
                    label="اولویت"
                    hint="اگر چند قانون هم‌نوع همزمان قابل اجرا باشند، قانون با اولویت بالاتر انتخاب می‌شود."
                >
                    <x-admin.form.input
                        name="priority"
                        type="number"
                        min="0"
                        max="65535"
                        dir="ltr"
                        :value="old('priority', $earningRule?->priority ?? 100)"
                    />
                </x-admin.field>

                <div class="md:col-span-2">
                    <label
                        class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div>
                            <div class="text-sm font-bold text-slate-800">فعال بودن قانون</div>
                            <p class="mt-1 text-xs leading-6 text-slate-500">
                                قانون غیرفعال حتی در بازه زمانی معتبر، هیچ امتیازی ایجاد نمی‌کند.
                            </p>
                        </div>

                        <x-admin.form.switch
                            name="is_active"
                            :checked="$isActive"
                        />
                    </label>
                </div>
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="تنظیمات امتیازدهی">
            <div
                class="mb-5 rounded-2xl border border-brand-100 bg-brand-50/60 px-4 py-3 text-xs leading-7 text-slate-700">
                <div x-show="ruleType === 'registration'" x-cloak>
                    این قانون یک بار و پس از ثبت نام واقعی کاربر در سایت بررسی می‌شود.
                </div>
                <div x-show="ruleType === 'daily_visit'" x-cloak>
                    این قانون فقط برای اولین بازدید واجد شرایط کاربر در هر روز اجرا می‌شود.
                </div>
                <div x-show="ruleType === 'purchase'" x-cloak>
                    امتیاز خرید پس از پرداخت موفق سفارش و بر اساس مبلغ نهایی محصولات بعد از تخفیف محاسبه می‌شود.
                </div>
                <div x-show="ruleType === 'comment'" x-cloak>
                    فقط دیدگاه تاییدشده امتیاز می‌گیرد و در صورت رد یا حذف دیدگاه، امتیاز آن برگشت داده می‌شود.
                </div>
                <div x-show="ruleType === 'time_spent'" x-cloak>
                    <x-admin.field
                        required
                        label="حداقل زمان حضور (ثانیه)"
                        hint="کاربر باید حداقل این مدت در صفحه فعال بماند تا امتیاز دریافت کند."
                    >
                        <x-admin.form.input
                            name="config[required_seconds]"
                            type="number"
                            min="10"
                            dir="ltr"
                            :value="$initialConfig['required_seconds'] ?? ''"
                            x-model.number="config.required_seconds"
                        />
                    </x-admin.field>
                </div>

                <div x-show="ruleType === 'time_spent'" x-cloak>
                    <x-admin.field
                        required
                        label="حداکثر دریافت روزانه"
                        hint="تعداد دفعاتی که کاربر در روز می‌تواند از این قانون امتیاز بگیرد."
                    >
                        <x-admin.form.input
                            name="config[daily_limit]"
                            type="number"
                            min="1"
                            dir="ltr"
                            :value="$initialConfig['daily_limit'] ?? ''"
                            x-model.number="config.daily_limit"
                        />
                    </x-admin.field>
                </div>

                <div x-show="ruleType === 'time_spent'" x-cloak class="md:col-span-2">
                    <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div>
                            <div class="text-sm font-bold text-slate-800">هر محتوا فقط یک بار در روز</div>
                            <p class="mt-1 text-xs leading-6 text-slate-500">
                                اگر فعال باشد، کاربر برای یک محصول یا نوشته مشخص در همان روز فقط یک بار امتیاز می‌گیرد.
                            </p>
                        </div>

                        <x-admin.form.switch
                            name="config[unique_target_daily]"
                            :checked="$initialConfig['unique_target_daily'] ?? false"
                            x-model="config.unique_target_daily"
                        />
                    </label>
                </div>

                <div x-show="ruleType === 'time_spent'" x-cloak>
                    این قانون بر اساس مدت حضور واقعی کاربر در صفحه محصول یا نوشته امتیاز ایجاد می‌کند.
                </div>
                <div x-show="ruleType === 'membership_anniversary'" x-cloak>
                    این قانون توسط پردازش روزانه سالگرد عضویت اجرا می‌شود.
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-admin.field required label="امتیاز">
                    <x-admin.form.input
                        name="config[points]"
                        type="number"
                        min="1"
                        dir="ltr"
                        :value="$initialConfig['points'] ?? ''"
                        x-model.number="config.points"
                    />
                </x-admin.field>

                <div x-show="ruleType === 'purchase'" x-cloak>
                    <x-admin.field
                        required
                        label="به ازای هر مبلغ (تومان)"
                        hint="مثلا اگر 100000 باشد، مبلغ سفارش به گام‌های صد هزار تومانی تقسیم می‌شود."
                    >
                        <x-admin.form.input
                            name="config[amount_step]"
                            type="number"
                            min="1"
                            dir="ltr"
                            :value="$initialConfig['amount_step'] ?? ''"
                            x-model.number="config.amount_step"
                        />
                    </x-admin.field>
                </div>

                <div x-show="ruleType === 'purchase'" x-cloak>
                    <x-admin.field label="حداقل مبلغ سفارش (تومان)">
                        <x-admin.form.input
                            name="config[minimum_order_amount]"
                            type="number"
                            min="0"
                            dir="ltr"
                            :value="$initialConfig['minimum_order_amount'] ?? 0"
                            x-model.number="config.minimum_order_amount"
                        />
                    </x-admin.field>
                </div>

                <div x-show="ruleType === 'purchase'" x-cloak>
                    <x-admin.field
                        label="حداکثر امتیاز هر سفارش"
                        hint="برای بدون سقف بودن، این فیلد را خالی بگذارید."
                    >
                        <x-admin.form.input
                            name="config[max_points_per_order]"
                            type="number"
                            min="1"
                            dir="ltr"
                            :value="$initialConfig['max_points_per_order'] ?? ''"
                            x-model.number="config.max_points_per_order"
                        />
                    </x-admin.field>
                </div>

                <div x-show="ruleType === 'comment'" x-cloak>
                    <x-admin.field
                        required
                        label="حداکثر دیدگاه امتیازدار در روز"
                        hint="پس از رسیدن کاربر به این تعداد، دیدگاه‌های بعدی همان روز امتیاز نمی‌گیرند."
                    >
                        <x-admin.form.input
                            name="config[daily_limit]"
                            type="number"
                            min="1"
                            dir="ltr"
                            :value="$initialConfig['daily_limit'] ?? ''"
                            x-model.number="config.daily_limit"
                        />
                    </x-admin.field>
                </div>

                <div x-show="ruleType === 'comment'" x-cloak class="md:col-span-2">
                    <label
                        class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div>
                            <div class="text-sm font-bold text-slate-800">پاسخ به دیدگاه هم امتیاز داشته باشد</div>
                            <p class="mt-1 text-xs leading-6 text-slate-500">
                                در حالت غیرفعال، فقط دیدگاه‌های اصلی در محاسبه امتیاز قرار می‌گیرند.
                            </p>
                        </div>

                        <x-admin.form.switch
                            name="config[include_replies]"
                            :checked="$initialConfig['include_replies'] ?? false"
                            x-model="config.include_replies"
                        />
                    </label>
                </div>

                <div x-show="ruleType === 'time_spent'" x-cloak>
                    <x-admin.field
                        required
                        label="حداقل زمان حضور (ثانیه)"
                        hint="کاربر باید حداقل این مدت در صفحه فعال بماند تا امتیاز دریافت کند."
                    >
                        <x-admin.form.input
                            name="config[required_seconds]"
                            type="number"
                            min="10"
                            dir="ltr"
                            :value="$initialConfig['required_seconds'] ?? ''"
                            x-model.number="config.required_seconds"
                        />
                    </x-admin.field>
                </div>

                <div x-show="ruleType === 'time_spent'" x-cloak>
                    <x-admin.field
                        required
                        label="حداکثر دریافت روزانه"
                        hint="تعداد دفعاتی که کاربر در روز می‌تواند از این قانون امتیاز بگیرد."
                    >
                        <x-admin.form.input
                            name="config[daily_limit]"
                            type="number"
                            min="1"
                            dir="ltr"
                            :value="$initialConfig['daily_limit'] ?? ''"
                            x-model.number="config.daily_limit"
                        />
                    </x-admin.field>
                </div>

                <div x-show="ruleType === 'time_spent'" x-cloak class="md:col-span-2">
                    <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div>
                            <div class="text-sm font-bold text-slate-800">هر محتوا فقط یک بار در روز</div>
                            <p class="mt-1 text-xs leading-6 text-slate-500">
                                اگر فعال باشد، کاربر برای یک محصول یا نوشته مشخص در همان روز فقط یک بار امتیاز می‌گیرد.
                            </p>
                        </div>

                        <x-admin.form.switch
                            name="config[unique_target_daily]"
                            :checked="$initialConfig['unique_target_daily'] ?? false"
                            x-model="config.unique_target_daily"
                        />
                    </label>
                </div>

                <div x-show="ruleType === 'time_spent'" x-cloak>
                    این قانون بر اساس مدت حضور واقعی کاربر در صفحه محصول یا نوشته امتیاز ایجاد می‌کند.
                </div>
                <div x-show="ruleType === 'membership_anniversary'" x-cloak>
                    <x-admin.field
                        required
                        label="حداقل سابقه عضویت (سال)"
                        hint="مثلا با مقدار 1، اولین سالگرد عضویت هم شامل امتیاز می‌شود."
                    >
                        <x-admin.form.input
                            name="config[minimum_membership_years]"
                            type="number"
                            min="1"
                            dir="ltr"
                            :value="$initialConfig['minimum_membership_years'] ?? ''"
                            x-model.number="config.minimum_membership_years"
                        />
                    </x-admin.field>
                </div>
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="بازه زمانی اجرا">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-admin.field
                    label="زمان شروع"
                    hint="اگر خالی باشد، قانون از نظر زمان شروع محدودیتی ندارد."
                >
                    <x-admin.form.date-picker
                        name="starts_at"
                        :value="old('starts_at', $earningRule?->starts_at ? jalali_date($earningRule->starts_at) : '')"
                    />
                </x-admin.field>

                <x-admin.field
                    label="زمان پایان"
                    hint="اگر خالی باشد، قانون از نظر زمان پایان محدودیتی ندارد."
                >
                    <x-admin.form.date-picker
                        name="ends_at"
                        :value="old('ends_at', $earningRule?->ends_at ? jalali_date($earningRule->ends_at) : '')"
                    />
                </x-admin.field>
            </div>

            <div
                class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-7 text-amber-800">
                اگر دو قانون از یک نوع در یک زمان فعال باشند، موتور امتیازدهی فقط قانونی را انتخاب می‌کند که اولویت
                بیشتری دارد. در اولویت برابر، قانون جدیدتر انتخاب می‌شود.
            </div>
        </x-admin.metabox>
    </div>
</x-admin.page>
