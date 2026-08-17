@extends('front.account.layout')

@php
    use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
    use App\Domain\Loyalty\Enums\PointEntryDirectionEnum;
    use App\Domain\Loyalty\Enums\PointEntryTypeEnum;

    $balance = $club['balance'];
    $spendableBalance = $club['spendable_balance'];
    $debt = $club['debt'];
    $lifetimeEarned = $club['lifetime_earned'];
    $lifetimeSpent = $club['lifetime_spent'];
    $entriesCount = $club['entries_count'];
    $earningRules = $club['earning_rules'];
    $entries = $club['entries'];
@endphp

@section('page-content')
    <div class="flex min-w-0 flex-col gap-4 sm:gap-6">
        {{-- Main club card --}}
        <x-front.cart
            class="relative overflow-hidden !border-brand-100 !bg-gradient-to-l !from-brand-50 !via-white !to-white !p-0 dark:!border-brand-500/20 dark:!from-brand-500/10 dark:!via-slate-900 dark:!to-slate-900"
        >
            <div class="pointer-events-none absolute -right-20 -top-24 h-56 w-56 rounded-full bg-brand-200/50 blur-3xl dark:bg-brand-500/10"></div>
            <div class="pointer-events-none absolute -bottom-20 -left-16 h-48 w-48 rounded-full bg-amber-100/70 blur-3xl dark:bg-amber-500/5"></div>

            <div class="relative p-4 sm:p-6 lg:p-7">
                <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-brand-200 bg-white/80 px-3 py-1.5 text-[11px] font-extrabold text-brand-700 shadow-sm dark:border-brand-500/20 dark:bg-slate-900/70 dark:text-brand-300 [&_svg]:h-4 [&_svg]:w-4">
                                @include('icons.award-03')
                                باشگاه مخاطبان مودینگ وی
                            </span>

                            @if($debt > 0)
                                <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-[11px] font-extrabold text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300">
                                    بدهی امتیاز داری
                                </span>
                            @endif
                        </div>

                        <h2 class="mt-4 text-xl font-black leading-8 text-slate-950 sm:text-2xl dark:text-white">
                            امتیازهات رو جمع کن و از مزایای باشگاه استفاده کن
                        </h2>

                        <p class="mt-2 max-w-2xl text-xs font-semibold leading-6 text-slate-600 sm:text-sm sm:leading-7 dark:text-slate-400">
                            فعالیت‌های واجد شرایط به صورت خودکار ثبت می‌شن. پایین همین صفحه هم می‌تونی راه‌های فعال کسب امتیاز و تاریخچه کامل تغییرات حسابت رو ببینی.
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a
                                href="#earning-methods"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-2xl bg-brand-500 px-4 text-xs font-extrabold text-white shadow-soft transition hover:bg-brand-600 dark:bg-brand-500 dark:hover:bg-brand-400 [&_svg]:h-4 [&_svg]:w-4"
                            >
                                راه‌های کسب امتیاز
                                @include('icons.arrow-down-01')
                            </a>

                            <a
                                href="#point-history"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white/80 px-4 text-xs font-extrabold text-slate-700 transition hover:border-brand-200 hover:bg-brand-50 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 [&_svg]:h-4 [&_svg]:w-4"
                            >
                                تاریخچه امتیازها
                                @include('icons.arrow-down-01')
                            </a>
                        </div>
                    </div>

                    <div class="w-full shrink-0 xl:w-[330px]">
                        <div class="rounded-3xl border border-white/80 bg-white/85 p-4 shadow-sm backdrop-blur sm:p-5 dark:border-slate-700/80 dark:bg-slate-900/80">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">امتیاز قابل استفاده</div>
                                    <div class="mt-2 flex items-end gap-2" dir="ltr">
                                        <strong class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl dark:text-white">
                                            {{ number_format($spendableBalance) }}
                                        </strong>
                                        <span class="pb-1 text-xs font-black text-brand-600 dark:text-brand-300">امتیاز</span>
                                    </div>
                                </div>

                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-3xl border border-brand-100 bg-brand-50 text-brand-600 shadow-sm dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-7 [&_svg]:w-7">
                                    @include('icons.medal-06')
                                </div>
                            </div>

                            @if($debt > 0)
                                <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-[11px] font-bold leading-5 text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300">
                                    {{ number_format($debt) }} امتیاز بدهی در حساب ثبت شده. امتیازهای بعدی ابتدا این مقدار رو جبران می‌کنن.
                                </div>
                            @else
                                <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-3 text-[11px] dark:border-slate-800">
                                    <span class="font-bold text-slate-500 dark:text-slate-400">موجودی ثبت شده</span>
                                    <span class="font-black text-slate-800 dark:text-slate-200">{{ number_format($balance) }} امتیاز</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </x-front.cart>

        {{-- Statistics --}}
        <section class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
            @foreach([
                [
                    'label' => 'کل امتیاز کسب شده',
                    'value' => $lifetimeEarned,
                    'desc' => 'مجموع امتیازهای واقعی دریافتی',
                    'icon' => 'icons.star',
                    'tone' => 'emerald',
                ],
                [
                    'label' => 'کل امتیاز مصرف شده',
                    'value' => $lifetimeSpent,
                    'desc' => 'مجموع امتیازهای استفاده شده',
                    'icon' => 'icons.wallet-03',
                    'tone' => 'amber',
                ],
                [
                    'label' => 'گردش‌های امتیاز',
                    'value' => $entriesCount,
                    'desc' => 'تعداد رکوردهای ثبت شده در حساب',
                    'icon' => 'icons.transaction',
                    'tone' => 'brand',
                ],
            ] as $stat)
                <div class="relative overflow-hidden rounded-2xl border bg-white p-4 shadow-sm sm:rounded-3xl sm:p-5 dark:bg-slate-900/90 dark:shadow-black/20
                    @if($stat['tone'] === 'emerald') border-emerald-100 dark:border-emerald-500/20
                    @elseif($stat['tone'] === 'amber') border-amber-100 dark:border-amber-500/20
                    @else border-brand-100 dark:border-brand-500/20
                    @endif">
                    <div class="pointer-events-none absolute -left-8 -top-8 h-20 w-20 rounded-full blur-2xl
                        @if($stat['tone'] === 'emerald') bg-emerald-100/70 dark:bg-emerald-500/10
                        @elseif($stat['tone'] === 'amber') bg-amber-100/70 dark:bg-amber-500/10
                        @else bg-brand-100/70 dark:bg-brand-500/10
                        @endif"></div>

                    <div class="relative flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</div>
                            <div class="mt-2 text-2xl font-black text-slate-950 sm:text-3xl dark:text-white">{{ number_format($stat['value']) }}</div>
                            <div class="mt-1.5 text-[10px] font-semibold leading-5 text-slate-500 sm:text-[11px] dark:text-slate-400">{{ $stat['desc'] }}</div>
                        </div>

                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border [&_svg]:h-5 [&_svg]:w-5
                            @if($stat['tone'] === 'emerald') border-emerald-100 bg-emerald-50 text-emerald-600 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300
                            @elseif($stat['tone'] === 'amber') border-amber-100 bg-amber-50 text-amber-600 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300
                            @else border-brand-100 bg-brand-50 text-brand-600 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300
                            @endif">
                            @include($stat['icon'])
                        </span>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- Ways to earn --}}
        <section id="earning-methods" class="scroll-mt-32">
            <x-front.cart class="!p-4 sm:!p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <x-front.cart-title
                        class="!mb-0"
                        title="راه‌های کسب امتیاز"
                        subtitle="قوانین فعال باشگاه که همین الان برای حساب شما قابل اجرا هستن"
                        icon-name="award-03"
                    />

                    @if($earningRules->isNotEmpty())
                        <span class="inline-flex w-fit items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-[10px] font-extrabold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                            {{ $earningRules->count() }} روش فعال
                        </span>
                    @endif
                </div>

                @if($earningRules->isEmpty())
                    <div class="mt-5 rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-center dark:border-slate-700 dark:bg-slate-800/50">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm dark:bg-slate-900 dark:text-slate-500 [&_svg]:h-6 [&_svg]:w-6">
                            @include('icons.award-03')
                        </div>
                        <div class="mt-3 text-sm font-black text-slate-800 dark:text-slate-200">فعلا روش فعالی برای کسب امتیاز تعریف نشده</div>
                        <div class="mx-auto mt-1 max-w-md text-xs font-semibold leading-6 text-slate-500 dark:text-slate-400">
                            به محض فعال شدن قوانین جدید، جزئیاتشون همین‌جا نمایش داده می‌شه.
                        </div>
                    </div>
                @else
                    <div class="mt-5 grid grid-cols-1 gap-3 md:grid-cols-2">
                        @foreach($earningRules as $rule)
                            @php
                                $config = $rule->config ?? [];

                                [$icon, $tone, $description, $meta] = match ($rule->type) {
                                    EarningRuleTypeEnum::REGISTRATION => [
                                        'icons.user-edit-01',
                                        'brand',
                                        'با تکمیل ثبت نام در سایت، امتیاز خوش آمدگویی دریافت می‌کنی.',
                                        number_format((int)($config['points'] ?? 0)) . ' امتیاز',
                                    ],
                                    EarningRuleTypeEnum::DAILY_VISIT => [
                                        'icons.calendar-03',
                                        'cyan',
                                        'اولین بازدید واجد شرایطت در هر روز می‌تونه برات امتیاز داشته باشه.',
                                        number_format((int)($config['points'] ?? 0)) . ' امتیاز در روز',
                                    ],
                                    EarningRuleTypeEnum::PURCHASE => [
                                        'icons.shopping-basket-03',
                                        'emerald',
                                        'با خرید موفق، بر اساس مبلغ نهایی واجد شرایط سفارشت امتیاز می‌گیری.',
                                        'هر ' . number_format((int)($config['amount_step'] ?? 0)) . ' تومان = ' . number_format((int)($config['points'] ?? 0)) . ' امتیاز',
                                    ],
                                    EarningRuleTypeEnum::COMMENT => [
                                        'icons.message-search-02',
                                        'violet',
                                        'دیدگاه‌های تایید شده می‌تونن برای حسابت امتیاز ثبت کنن.',
                                        number_format((int)($config['points'] ?? 0)) . ' امتیاز، تا ' . number_format((int)($config['daily_limit'] ?? 0)) . ' بار در روز',
                                    ],
                                    EarningRuleTypeEnum::MEMBERSHIP_ANNIVERSARY => [
                                        'icons.crown-03',
                                        'amber',
                                        'در سالگرد عضویتت، اگر شرایط قانون رو داشته باشی امتیاز هدیه می‌گیری.',
                                        number_format((int)($config['points'] ?? 0)) . ' امتیاز سالگرد',
                                    ],
                                };
                            @endphp

                            <article class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-slate-50/70 p-4 transition duration-300 hover:-translate-y-0.5 hover:bg-white hover:shadow-soft dark:border-slate-700 dark:bg-slate-800/50 dark:hover:bg-slate-800 dark:hover:shadow-black/20">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border [&_svg]:h-5 [&_svg]:w-5
                                        @if($tone === 'emerald') border-emerald-100 bg-emerald-50 text-emerald-600 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300
                                        @elseif($tone === 'cyan') border-cyan-100 bg-cyan-50 text-cyan-600 dark:border-cyan-500/20 dark:bg-cyan-500/10 dark:text-cyan-300
                                        @elseif($tone === 'violet') border-violet-100 bg-violet-50 text-violet-600 dark:border-violet-500/20 dark:bg-violet-500/10 dark:text-violet-300
                                        @elseif($tone === 'amber') border-amber-100 bg-amber-50 text-amber-600 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300
                                        @else border-brand-100 bg-brand-50 text-brand-600 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300
                                        @endif">
                                        @include($icon)
                                    </span>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                            <h3 class="text-sm font-black text-slate-900 dark:text-white">{{ $rule->title }}</h3>
                                            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black text-brand-700 shadow-sm dark:bg-slate-900 dark:text-brand-300">{{ $meta }}</span>
                                        </div>

                                        <p class="mt-2 text-[11px] font-semibold leading-6 text-slate-500 dark:text-slate-400">{{ $description }}</p>

                                        @if($rule->type === EarningRuleTypeEnum::PURCHASE && (int)($config['minimum_order_amount'] ?? 0) > 0)
                                            <div class="mt-2 text-[10px] font-bold text-slate-500 dark:text-slate-400">
                                                حداقل مبلغ سفارش: {{ number_format((int)$config['minimum_order_amount']) }} تومان
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </x-front.cart>
        </section>

        {{-- Point history --}}
        <section id="point-history" class="scroll-mt-32">
            <x-front.cart class="!overflow-hidden !p-0">
                <div class="border-b border-slate-200 p-4 sm:p-5 dark:border-slate-800">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <x-front.cart-title
                            class="!mb-0"
                            title="تاریخچه امتیازها"
                            subtitle="تمام افزایش‌ها، مصرف‌ها، برگشت‌ها و اصلاحات حساب امتیاز"
                            icon-name="transaction"
                        />

                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">
                            {{ number_format($entries->total()) }} گردش ثبت شده
                        </span>
                    </div>
                </div>

                @forelse($entries as $entry)
                    @php
                        $isCredit = $entry->direction === PointEntryDirectionEnum::CREDIT;
                        $signedAmount = $isCredit ? $entry->amount : -$entry->amount;
                        $reasonLabel = match ($entry->reason) {
                            EarningRuleTypeEnum::REGISTRATION->value => 'ثبت نام',
                            EarningRuleTypeEnum::DAILY_VISIT->value => 'بازدید روزانه',
                            EarningRuleTypeEnum::PURCHASE->value => 'خرید',
                            EarningRuleTypeEnum::COMMENT->value => 'ثبت دیدگاه',
                            EarningRuleTypeEnum::MEMBERSHIP_ANNIVERSARY->value => 'سالگرد عضویت',
                            'manual_adjustment' => 'اصلاح دستی',
                            'reversal' => 'برگشت امتیاز',
                            default => $entry->type->label(),
                        };

                        $icon = match ($entry->type) {
                            PointEntryTypeEnum::EARN => 'icons.star',
                            PointEntryTypeEnum::SPEND => 'icons.wallet-03',
                            PointEntryTypeEnum::REVERSAL => 'icons.arrow-turn-backward',
                            PointEntryTypeEnum::EXPIRATION => 'icons.calendar-03',
                            PointEntryTypeEnum::ADJUSTMENT => 'icons.transaction',
                        };
                    @endphp

                    <article class="border-b border-slate-100 p-4 last:border-b-0 sm:p-5 dark:border-slate-800">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl [&_svg]:h-5 [&_svg]:w-5
                                    @if($isCredit) bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300
                                    @else bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300
                                    @endif">
                                    @include($icon)
                                </span>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-black text-slate-900 dark:text-white">{{ $entry->description ?: $reasonLabel }}</h3>
                                        <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[9px] font-extrabold text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                                            {{ $entry->type->label() }}
                                        </span>
                                    </div>

                                    <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-[10px] font-semibold text-slate-500 dark:text-slate-400">
                                        <span>{{ $reasonLabel }}</span>
                                        <span class="hidden h-1 w-1 rounded-full bg-slate-300 sm:block dark:bg-slate-600"></span>
                                        <span>{{ jalali_date($entry->occurred_at ?? $entry->created_at, 'Y/m/d - H:i') }}</span>
                                        @if($entry->earningRule)
                                            <span class="hidden h-1 w-1 rounded-full bg-slate-300 sm:block dark:bg-slate-600"></span>
                                            <span>{{ $entry->earningRule->title }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-100 bg-slate-50 px-3 py-2.5 sm:min-w-[190px] sm:justify-end sm:border-0 sm:bg-transparent sm:p-0 dark:border-slate-700 dark:bg-slate-800/60 sm:dark:bg-transparent">
                                <div class="text-right sm:text-left">
                                    <div class="text-base font-black sm:text-lg {{ $isCredit ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300' }}" dir="ltr">
                                        {{ $isCredit ? '+' : '-' }}{{ number_format($entry->amount) }}
                                    </div>
                                    <div class="mt-0.5 text-[9px] font-bold text-slate-400 dark:text-slate-500">امتیاز</div>
                                </div>

                                <div class="h-8 w-px bg-slate-200 dark:bg-slate-700"></div>

                                <div class="text-left">
                                    <div class="text-[9px] font-bold text-slate-400 dark:text-slate-500">مانده بعد</div>
                                    <div class="mt-0.5 text-xs font-black text-slate-700 dark:text-slate-300">{{ number_format($entry->balance_after) }}</div>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="px-4 py-10 text-center sm:px-6 sm:py-14">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl border border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500 [&_svg]:h-7 [&_svg]:w-7">
                            @include('icons.transaction')
                        </div>
                        <h3 class="mt-4 text-sm font-black text-slate-900 dark:text-white">هنوز گردش امتیازی ثبت نشده</h3>
                        <p class="mx-auto mt-1.5 max-w-md text-xs font-semibold leading-6 text-slate-500 dark:text-slate-400">
                            بعد از انجام یکی از فعالیت‌های واجد شرایط، اولین رکورد امتیاز اینجا نمایش داده می‌شه.
                        </p>
                    </div>
                @endforelse

                @if($entries->hasPages())
                    <div class="border-t border-slate-100 px-4 py-4 sm:px-5 dark:border-slate-800">
                        {{ $entries->onEachSide(1)->links() }}
                    </div>
                @endif
            </x-front.cart>
        </section>
    </div>
@endsection
