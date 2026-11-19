@php
    use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;

    $drawCampaign = $drawCampaign ?? null;
    $structureEditable = !$drawCampaign || (
        in_array($drawCampaign->status, [DrawCampaignStatusEnum::DRAFT, DrawCampaignStatusEnum::SCHEDULED], true)
        && (int)$drawCampaign->issued_tickets_count === 0
    );
    $isPubliclyVisible = $drawCampaign && !in_array($drawCampaign->status, [
        DrawCampaignStatusEnum::DRAFT,
        DrawCampaignStatusEnum::CANCELLED,
    ], true);
@endphp

<x-admin.page columns="2">
    <x-slot:sidebar>
        @if(!$drawCampaign)
            <x-admin.metabox title="وضعیت قرعه کشی" :collapsible="false">
                <x-admin.badge color="slate" size="md">پیش نویس</x-admin.badge>
                <p class="mt-3 text-xs leading-7 text-slate-500">قرعه کشی جدید همیشه به صورت پیش نویس ساخته می‌شود.</p>
            </x-admin.metabox>
        @endif

        <x-admin.metabox title="کاور قرعه کشی" class="overflow-visible">
            <x-admin.form.media-picker
                mode="field"
                name="cover"
                :value="[
                    'id' => old('cover.id', $drawCampaign?->cover_media_file_id),
                    'path' => old('cover.path', $drawCampaign?->coverMediaFile?->url),
                ]"
                layout="vertical"
            />
        </x-admin.metabox>

        <x-admin.metabox title="نمایش نتیجه">
            <x-admin.form.switch
                name="show_winners_publicly"
                label="نمایش عمومی برندگان"
                :checked="old('show_winners_publicly', $drawCampaign?->show_winners_publicly ?? false)"
            />
            <p class="mt-2 text-xs leading-6 text-slate-500">نام و کد بلیط برندگان در صفحه عمومی به‌صورت ماسک‌شده نمایش داده می‌شود.</p>
        </x-admin.metabox>
    </x-slot:sidebar>

    <x-admin.form.validation-errors class="mb-6"/>

    <x-admin.form.wp-title-slug
        titleName="title"
        slugName="slug"
        :titleValue="$drawCampaign?->title ?? ''"
        :slugValue="$drawCampaign?->slug ?? ''"
        :basePath="rtrim(route('raffles.index'), '/') . '/'"
        :scope="\App\Domain\Slug\Enums\SlugScopeEnum::RAFFLE"
        :currentId="$drawCampaign?->id"
        :itemUrl="$isPubliclyVisible ? route('raffles.show', $drawCampaign->slug) : null"
    />

    <x-admin.metabox title="خلاصه قرعه کشی">
        <x-admin.field label="توضیح کوتاه" hint="این متن در کارت و معرفی کوتاه قرعه کشی نمایش داده می‌شود.">
            <x-admin.form.textarea name="short_description" rows="3" :value="old('short_description', $drawCampaign?->short_description)"/>
        </x-admin.field>
    </x-admin.metabox>

    <x-admin.metabox title="معرفی قرعه کشی">
        <x-admin.form.editor name="body" :value="old('body', $drawCampaign?->body)"/>
    </x-admin.metabox>

    <x-admin.metabox title="قوانین شرکت">
        <x-admin.form.editor name="rules" :value="old('rules', $drawCampaign?->rules)"/>
    </x-admin.metabox>

    @if($structureEditable)
        <x-admin.metabox title="زمان بندی و ظرفیت">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <x-admin.field label="زمان شروع">
                    <x-admin.form.date-picker name="starts_at" :value="old('starts_at', $drawCampaign?->starts_at ? jalali_date($drawCampaign->starts_at, 'Y/m/d H:i') : '')"/>
                </x-admin.field>
                <x-admin.field label="زمان پایان دریافت بلیط">
                    <x-admin.form.date-picker name="closes_at" :value="old('closes_at', $drawCampaign?->closes_at ? jalali_date($drawCampaign->closes_at, 'Y/m/d H:i') : '')"/>
                </x-admin.field>
                <x-admin.field label="زمان برنامه ریزی شده قرعه کشی">
                    <x-admin.form.date-picker name="draw_at" :value="old('draw_at', $drawCampaign?->draw_at ? jalali_date($drawCampaign->draw_at, 'Y/m/d H:i') : '')"/>
                </x-admin.field>
                <x-admin.field label="ظرفیت کل بلیط" hint="خالی یعنی نامحدود.">
                    <x-admin.form.input name="ticket_limit" type="number" min="1" dir="ltr" :value="old('ticket_limit', $drawCampaign?->ticket_limit)"/>
                </x-admin.field>
                <x-admin.field label="سقف بلیط هر کاربر" hint="خالی یعنی نامحدود.">
                    <x-admin.form.input name="ticket_limit_per_user" type="number" min="1" dir="ltr" :value="old('ticket_limit_per_user', $drawCampaign?->ticket_limit_per_user)"/>
                </x-admin.field>
                <x-admin.field required label="برنده ذخیره پیش فرض برای هر جایزه">
                    <x-admin.form.input name="reserve_winners_per_prize" type="number" min="0" max="100" dir="ltr" :value="old('reserve_winners_per_prize', $drawCampaign?->reserve_winners_per_prize ?? 3)"/>
                </x-admin.field>
                <x-admin.field label="مهلت تایید برنده (ساعت)">
                    <x-admin.form.input name="winner_confirmation_window_hours" type="number" min="1" dir="ltr" :value="old('winner_confirmation_window_hours', $drawCampaign?->winner_confirmation_window_hours ?? 72)"/>
                </x-admin.field>
                <div class="md:col-span-2">
                    <x-admin.form.switch
                        name="allow_multiple_wins_per_user"
                        label="اجازه چند برد برای یک کاربر"
                        :checked="old('allow_multiple_wins_per_user', $drawCampaign?->allow_multiple_wins_per_user ?? false)"
                    />
                    <p class="mt-2 text-xs leading-6 text-slate-500">پیش فرض خاموش است؛ در این حالت یک کاربر در یک اجرای قرعه کشی فقط یک جایزه اصلی خواهد داشت.</p>
                </div>
            </div>
        </x-admin.metabox>

        @include('admin.raffle.campaigns.partials.prizes')
    @else
        <x-admin.metabox title="تنظیمات قفل شده">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-7 text-amber-800">
                پس از صدور اولین بلیط، جوایز، ظرفیت و زمان‌بندی قفل می‌شوند. متن معرفی و قوانین همچنان قابل ویرایش است.
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="rounded-2xl bg-slate-50 p-3"><div class="text-[11px] text-slate-500">ظرفیت کل</div><div class="mt-1 text-sm font-black text-slate-800">{{ $drawCampaign->ticket_limit ? number_format($drawCampaign->ticket_limit) : 'نامحدود' }}</div></div>
                <div class="rounded-2xl bg-slate-50 p-3"><div class="text-[11px] text-slate-500">سقف هر کاربر</div><div class="mt-1 text-sm font-black text-slate-800">{{ $drawCampaign->ticket_limit_per_user ? number_format($drawCampaign->ticket_limit_per_user) : 'نامحدود' }}</div></div>
                <div class="rounded-2xl bg-slate-50 p-3"><div class="text-[11px] text-slate-500">جوایز</div><div class="mt-1 text-sm font-black text-slate-800">{{ number_format($drawCampaign->prizes->sum('quantity')) }} مورد</div></div>
                <div class="rounded-2xl bg-slate-50 p-3"><div class="text-[11px] text-slate-500">بلیط صادر شده</div><div class="mt-1 text-sm font-black text-slate-800">{{ number_format($drawCampaign->issued_tickets_count) }}</div></div>
            </div>
        </x-admin.metabox>
    @endif

    <x-admin.metabox title="هزینه بلیط">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-admin.field label="امتیاز برای هر بلیط">
                <x-admin.form.input name="points_per_ticket" type="number" min="1" dir="ltr" :value="old('points_per_ticket', $drawCampaign?->points_per_ticket ?? 100)"/>
            </x-admin.field>
            <x-admin.field label="قیمت نقدی هر بلیط (تومان)">
                <x-admin.form.input name="ticket_price" type="number" min="0" dir="ltr" :value="old('ticket_price', $drawCampaign?->ticket_price)"/>
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="شرایط واجد شرایط بودن" class="mt-5">
        <div class="mb-3 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-2.5 text-xs font-bold leading-6 text-sky-700 dark:border-sky-500/20 dark:bg-sky-500/5 dark:text-sky-300">
            کاربر فقط وقتی می‌تواند کد قرعه‌کشی بگیرد که همهٔ شرط‌های فعال را داشته باشد. مقدار ۰ = بدون شرط.
        </div>
        @php($rules = old('eligibility_rules', $drawCampaign?->eligibility_rules ?? []))
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <x-admin.field label="حداقل دعوت ثبت‌نام‌شده" hint="تعداد دوستانی که با لینک شما ثبت‌نام کرده‌اند">
                <x-admin.form.input name="eligibility_rules[min_referrals]" type="number" min="0" dir="ltr"
                                    :value="old('eligibility_rules.min_referrals', $rules['min_referrals'] ?? 0)"/>
            </x-admin.field>
            <x-admin.field label="حداقل خرید دعوت‌شدگان" hint="خرید انجام‌شده توسط دعوت‌شدگان شما">
                <x-admin.form.input name="eligibility_rules[min_referral_orders]" type="number" min="0" dir="ltr"
                                    :value="old('eligibility_rules.min_referral_orders', $rules['min_referral_orders'] ?? 0)"/>
            </x-admin.field>
            <x-admin.field label="حداقل خرید شخصی" hint="تعداد خرید خود کاربر">
                <x-admin.form.input name="eligibility_rules[min_own_orders]" type="number" min="0" dir="ltr"
                                    :value="old('eligibility_rules.min_own_orders', $rules['min_own_orders'] ?? 0)"/>
            </x-admin.field>
            <x-admin.field label="حداقل XP باشگاه" hint="مثلاً 2000 = سطح بازیکن">
                <x-admin.form.input name="eligibility_rules[min_xp]" type="number" min="0" dir="ltr"
                                    :value="old('eligibility_rules.min_xp', $rules['min_xp'] ?? 0)"/>
            </x-admin.field>
            <x-admin.field label="حداقل دیدگاه تأییدشده">
                <x-admin.form.input name="eligibility_rules[min_comments]" type="number" min="0" dir="ltr"
                                    :value="old('eligibility_rules.min_comments', $rules['min_comments'] ?? 0)"/>
            </x-admin.field>
            <x-admin.field label="حداقل رسانه تأییدشده" hint="تصویر/ویدیو در گالری کاربران">
                <x-admin.form.input name="eligibility_rules[min_approved_media]" type="number" min="0" dir="ltr"
                                    :value="old('eligibility_rules.min_approved_media', $rules['min_approved_media'] ?? 0)"/>
            </x-admin.field>
            <x-admin.field label="حداقل عمر حساب (روز)">
                <x-admin.form.input name="eligibility_rules[min_account_days]" type="number" min="0" dir="ltr"
                                    :value="old('eligibility_rules.min_account_days', $rules['min_account_days'] ?? 0)"/>
            </x-admin.field>
                <x-admin.field label="موبایل تأییدشده الزامی" class="switch-field">
                    <x-admin.form.switch name="eligibility_rules[require_verified_mobile]"
                                          :checked="old('eligibility_rules.require_verified_mobile', $rules['require_verified_mobile'] ?? false)"/>
                </x-admin.field>
                <x-admin.field label="فقط اعضای VIP 👑" class="switch-field" hint="قرعه‌کشی مخصوص اعضای VIP">
                    <x-admin.form.switch name="eligibility_rules[require_vip]"
                                          :checked="old('eligibility_rules.require_vip', $rules['require_vip'] ?? false)"/>
                </x-admin.field>
            </div>

            <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50/60 p-4 dark:border-amber-500/25 dark:bg-amber-500/5">
                <x-admin.form.switch name="vip_monthly_ticket"
                                     label="هدیه ماهانه VIP — ۱ بلیط رایگان"
                                     :checked="old('vip_monthly_ticket', $drawCampaign?->vip_monthly_ticket ?? false)"/>
                <p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
                    هر عضو VIP فعال در هر ماه یک بلیط رایگان در این قرعه‌کشی هدیه می‌گیرد. شرط‌های واجد شرایط بودن همچنان رعایت می‌شود.
                </p>
            </div>
    </x-admin.metabox>
</x-admin.page>
