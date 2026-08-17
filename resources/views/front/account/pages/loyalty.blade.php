@extends('front.account.layout')

@section('page-content')
    @php
        use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
        use App\Domain\Loyalty\Enums\RewardRedemptionStatusEnum;
        use App\Domain\Loyalty\Enums\RewardTypeEnum;
        use App\Domain\Promotion\Enums\CouponTypeEnum;
        use App\Domain\Raffle\Models\DrawCampaign;
        use App\Domain\Raffle\Models\TicketGrant;
        use Illuminate\Support\Str;
    @endphp

    <div class="space-y-6">
        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold leading-7 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold leading-7 text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-300">
                {{ session('error') }}
            </div>
        @endif

        <x-front.cart class="relative overflow-hidden !p-0 dark:border-slate-700 dark:bg-slate-900">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-50 via-white to-amber-50/70 dark:from-slate-900 dark:via-slate-900 dark:to-brand-950/20"></div>
            <div class="absolute -left-16 -top-16 h-44 w-44 rounded-full bg-brand-200/50 blur-3xl dark:bg-brand-900/20"></div>
            <div class="absolute -bottom-20 right-10 h-48 w-48 rounded-full bg-amber-200/50 blur-3xl dark:bg-amber-900/10"></div>

            <div class="relative p-5 sm:p-6 lg:p-8">
                <div class="grid gap-6 lg:grid-cols-12 lg:items-center">
                    <div class="lg:col-span-7">
                        <div class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white/80 px-3 py-1.5 text-xs font-extrabold text-brand-700 backdrop-blur dark:border-brand-900/60 dark:bg-slate-900/70 dark:text-brand-300">
                            باشگاه مخاطبان مودینگ وی
                        </div>

                        <h1 class="mt-4 text-xl font-black leading-9 text-slate-900 sm:text-2xl dark:text-slate-100">
                            امتیازت رو به تجربه های جذاب تبدیل کن
                        </h1>
                        <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-300">
                            با فعالیت در مودینگ وی امتیاز بگیر، پاداش دریافت کن و حالا با امتیازت بلیط واقعی قرعه کشی هم تهیه کن.
                        </p>
                    </div>

                    <div class="lg:col-span-5">
                        <div class="rounded-3xl border border-white/70 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-950/60">
                            <div class="text-xs font-bold text-slate-500 dark:text-slate-400">امتیاز قابل استفاده</div>
                            <div class="mt-2 flex items-end gap-2">
                                <div class="text-3xl font-black tracking-tight text-brand-700 dark:text-brand-300">{{ number_format($spendablePointBalance) }}</div>
                                <div class="pb-1 text-xs font-bold text-slate-400">امتیاز</div>
                            </div>

                            @if($pointBalance < 0)
                                <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs leading-6 text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-300">
                                    موجودی حساب شما {{ number_format(abs($pointBalance)) }} امتیاز بدهکار است. امتیازهای بعدی ابتدا این بدهی را پوشش می دهند.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </x-front.cart>

        <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
            @foreach([
                ['label' => 'امتیاز فعلی', 'value' => $pointBalance, 'suffix' => 'امتیاز'],
                ['label' => 'کل کسب شده', 'value' => $lifetimeEarned, 'suffix' => 'امتیاز'],
                ['label' => 'کل مصرف شده', 'value' => $lifetimeSpent, 'suffix' => 'امتیاز'],
                ['label' => 'پاداش های فعال من', 'value' => $completedRewardCount, 'suffix' => 'دریافت'],
            ] as $stat)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</div>
                    <div class="mt-2 flex items-end gap-1.5">
                        <div class="text-xl font-black {{ $stat['value'] < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-slate-100' }}">{{ number_format($stat['value']) }}</div>
                        <div class="pb-0.5 text-[10px] font-bold text-slate-400">{{ $stat['suffix'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <section id="rewards" class="scroll-mt-24">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-base font-black text-slate-900 sm:text-lg dark:text-slate-100">پاداش های قابل دریافت</h2>
                    <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">امتیازت را خرج کن؛ تخفیف بگیر یا شانس حضور در قرعه کشی ها را بیشتر کن.</p>
                </div>
                <a href="#ways-to-earn" class="text-xs font-extrabold text-brand-600 transition hover:text-brand-700 dark:text-brand-400">راه های کسب امتیاز</a>
            </div>

            @if($rewardOffers->isEmpty())
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center dark:border-slate-700 dark:bg-slate-900">
                    <div class="text-sm font-black text-slate-800 dark:text-slate-200">فعلا پاداش فعالی نداریم</div>
                    <p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">به زودی گزینه های جدیدی برای خرج کردن امتیاز اضافه می شود.</p>
                </div>
            @else
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($rewardOffers as $rewardOffer)
                        @php
                            $availability = $rewardAvailabilities->get($rewardOffer->getKey());
                            $isRaffle = $rewardOffer->type === RewardTypeEnum::RAFFLE_TICKET;
                            $campaign = $isRaffle && $rewardOffer->target instanceof DrawCampaign ? $rewardOffer->target : null;
                            $ticketQuantity = (int)($rewardOffer->config['ticket_quantity'] ?? 0);
                            $missingPoints = (int) data_get($availability?->context, 'missing_points', 0);
                            $couponType = data_get($rewardOffer->config, 'coupon_type');
                        @endphp

                        <article class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-400 via-brand-500 to-amber-400"></div>
                            <div class="p-5 sm:p-6">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full border border-brand-100 bg-brand-50 px-2.5 py-1 text-[10px] font-extrabold text-brand-700 dark:border-brand-900/60 dark:bg-brand-950/30 dark:text-brand-300">
                                                {{ $rewardOffer->type->label() }}
                                            </span>
                                            @if($isRaffle && $campaign)
                                                <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-extrabold text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-300">
                                                    {{ $campaign->status->label() }}
                                                </span>
                                            @endif
                                        </div>

                                        <h3 class="mt-3 text-base font-black leading-7 text-slate-900 dark:text-slate-100">{{ $rewardOffer->title }}</h3>
                                        @if($rewardOffer->description)
                                            <p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">{{ $rewardOffer->description }}</p>
                                        @endif
                                    </div>

                                    <div class="shrink-0 rounded-2xl bg-brand-50 px-3 py-2 text-center dark:bg-brand-950/30">
                                        <div class="text-lg font-black text-brand-700 dark:text-brand-300">{{ number_format($rewardOffer->points_cost) }}</div>
                                        <div class="text-[9px] font-bold text-brand-500">امتیاز</div>
                                    </div>
                                </div>

                                @if($isRaffle && $campaign)
                                    <a href="{{ route('raffles.show', $campaign->slug) }}" class="mt-4 block rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-brand-200 hover:bg-brand-50/40 dark:border-slate-700 dark:bg-slate-950/50 dark:hover:border-brand-900/70">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="text-[10px] font-bold text-slate-400">قرعه کشی مقصد</div>
                                                <div class="mt-1 truncate text-sm font-black text-slate-800 dark:text-slate-200">{{ $campaign->title }}</div>
                                            </div>
                                            <div class="shrink-0 rounded-xl bg-white px-3 py-2 text-center shadow-sm dark:bg-slate-900">
                                                <div class="text-base font-black text-amber-600 dark:text-amber-400">{{ number_format($ticketQuantity) }}</div>
                                                <div class="text-[9px] font-bold text-slate-400">بلیط</div>
                                            </div>
                                        </div>
                                    </a>
                                @elseif(!$isRaffle)
                                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                                        <div class="rounded-2xl bg-slate-50 px-3 py-3 dark:bg-slate-950/50">
                                            <div class="text-[10px] text-slate-400">مقدار تخفیف</div>
                                            <div class="mt-1 font-black text-slate-800 dark:text-slate-200">
                                                @if($couponType === CouponTypeEnum::PERCENTAGE->value)
                                                    {{ number_format((int)data_get($rewardOffer->config, 'coupon_value')) }}٪
                                                @else
                                                    {{ number_format((int)data_get($rewardOffer->config, 'coupon_value')) }} تومان
                                                @endif
                                            </div>
                                        </div>
                                        <div class="rounded-2xl bg-slate-50 px-3 py-3 dark:bg-slate-950/50">
                                            <div class="text-[10px] text-slate-400">اعتبار</div>
                                            <div class="mt-1 font-black text-slate-800 dark:text-slate-200">
                                                {{ data_get($rewardOffer->config, 'expires_after_days') ? number_format((int)data_get($rewardOffer->config, 'expires_after_days')) . ' روز' : 'بدون انقضا' }}
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="mt-5">
                                    @if($availability?->canRedeem)
                                        <form method="post" action="{{ route('account.loyalty.rewards.redeem', $rewardOffer) }}">
                                            @csrf
                                            <input type="hidden" name="idempotency_key" value="{{ (string) Str::uuid() }}">
                                            <button type="submit" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-2xl bg-brand-500 px-4 text-xs font-extrabold text-white shadow-sm transition hover:bg-brand-600 active:bg-brand-700 dark:bg-brand-600 dark:hover:bg-brand-500">
                                                {{ $isRaffle ? 'دریافت بلیط با امتیاز' : 'دریافت پاداش' }}
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" disabled class="inline-flex h-11 w-full cursor-not-allowed items-center justify-center rounded-2xl bg-slate-100 px-4 text-xs font-extrabold text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                                            فعلا قابل دریافت نیست
                                        </button>
                                        @if($availability?->reason)
                                            <div class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] leading-6 text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-300">
                                                {{ $availability->reason }}
                                                @if($missingPoints > 0)
                                                    <span class="font-black">{{ number_format($missingPoints) }} امتیاز</span>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section>
            <div class="mb-4">
                <h2 class="text-base font-black text-slate-900 sm:text-lg dark:text-slate-100">پاداش های من</h2>
                <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">تاریخچه کدهای تخفیف و بلیط هایی که با امتیاز دریافت کرده ای.</p>
            </div>

            <div class="space-y-3">
                @forelse($rewardRedemptions as $redemption)
                    @php
                        $isReversed = $redemption->status === RewardRedemptionStatusEnum::REVERSED;
                        $fulfillment = $redemption->fulfillment;
                        $grant = $fulfillment instanceof TicketGrant ? $fulfillment : null;
                    @endphp
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900 sm:p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="text-sm font-black text-slate-900 dark:text-slate-100">{{ $redemption->rewardTitle() }}</div>
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold {{ $isReversed ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300' }}">
                                        {{ $redemption->status->label() }}
                                    </span>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-slate-400">
                                    <span>{{ number_format($redemption->points_spent) }} امتیاز</span>
                                    <span>{{ jalali_date($redemption->created_at, 'Y/m/d H:i') }}</span>
                                </div>
                            </div>

                            @if($redemption->coupon)
                                <div x-data="{ copied: false }" class="flex items-center gap-2 rounded-xl bg-slate-50 p-2 dark:bg-slate-950/60">
                                    <code class="px-2 text-xs font-black text-slate-700 dark:text-slate-200" dir="ltr">{{ $redemption->coupon->code }}</code>
                                    <button type="button" x-on:click="navigator.clipboard.writeText(@js($redemption->coupon->code)); copied = true; setTimeout(() => copied = false, 1500)" class="rounded-lg bg-white px-2.5 py-1.5 text-[10px] font-bold text-brand-600 shadow-sm dark:bg-slate-900 dark:text-brand-400">
                                        <span x-show="!copied">کپی</span><span x-show="copied" x-cloak>کپی شد</span>
                                    </button>
                                </div>
                            @elseif($grant)
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="rounded-xl bg-amber-50 px-3 py-2 text-xs font-black text-amber-700 dark:bg-amber-950/20 dark:text-amber-300">
                                        {{ number_format($grant->quantity) }} بلیط
                                    </div>
                                    <a href="{{ route('account.raffles') }}" class="inline-flex h-9 items-center rounded-xl border border-slate-200 bg-white px-3 text-[11px] font-extrabold text-slate-700 transition hover:border-brand-200 hover:text-brand-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                        مشاهده بلیط ها
                                    </a>
                                </div>
                            @endif
                        </div>

                        @if($isReversed)
                            <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-[11px] leading-6 text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/20 dark:text-rose-300">
                                امتیاز این دریافت به حساب شما برگشت داده شده است.
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center dark:border-slate-700 dark:bg-slate-900">
                        <div class="text-sm font-black text-slate-800 dark:text-slate-200">هنوز پاداشی دریافت نکردی</div>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">از گزینه های بالا اولین پاداشت را انتخاب کن.</p>
                    </div>
                @endforelse
            </div>

            @if($rewardRedemptions->hasPages())
                <div class="mt-4">{{ $rewardRedemptions->fragment('rewards')->links() }}</div>
            @endif
        </section>

        <section id="ways-to-earn" class="scroll-mt-24">
            <div class="mb-4">
                <h2 class="text-base font-black text-slate-900 sm:text-lg dark:text-slate-100">راه های کسب امتیاز</h2>
                <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">قوانین فعال باشگاه مستقیما از تنظیمات ادمین نمایش داده می شوند.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($earningRules as $rule)
                    @php($cfg = $rule->config ?? [])
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                        <div class="text-sm font-black text-slate-900 dark:text-slate-100">{{ $rule->title }}</div>
                        <div class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
                            @switch($rule->type)
                                @case(EarningRuleTypeEnum::PURCHASE)
                                    هر {{ number_format((int)($cfg['amount_step'] ?? 0)) }} تومان خرید، {{ number_format((int)($cfg['points'] ?? 0)) }} امتیاز
                                    @break
                                @case(EarningRuleTypeEnum::COMMENT)
                                    هر دیدگاه تایید شده {{ number_format((int)($cfg['points'] ?? 0)) }} امتیاز
                                    @if(!empty($cfg['daily_limit'])) · حداکثر {{ number_format((int)$cfg['daily_limit']) }} بار در روز @endif
                                    @break
                                @case(EarningRuleTypeEnum::DAILY_VISIT)
                                    {{ number_format((int)($cfg['points'] ?? 0)) }} امتیاز برای اولین بازدید روزانه
                                    @break
                                @case(EarningRuleTypeEnum::REGISTRATION)
                                    {{ number_format((int)($cfg['points'] ?? 0)) }} امتیاز هدیه عضویت
                                    @break
                                @case(EarningRuleTypeEnum::MEMBERSHIP_ANNIVERSARY)
                                    {{ number_format((int)($cfg['points'] ?? 0)) }} امتیاز هدیه سالگرد عضویت
                                    @break
                                @default
                                    با انجام این فعالیت امتیاز دریافت می کنی.
                            @endswitch
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section id="point-history" class="scroll-mt-24">
            <div class="mb-4">
                <h2 class="text-base font-black text-slate-900 sm:text-lg dark:text-slate-100">تاریخچه امتیاز</h2>
                <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">تمام کسب، مصرف، برگشت و اصلاحات امتیاز حساب شما.</p>
            </div>

            <div class="space-y-2">
                @forelse($pointEntries as $entry)
                    @php($credit = $entry->direction->isCredit())
                    <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900">
                        <div class="min-w-0">
                            <div class="truncate text-xs font-extrabold text-slate-800 dark:text-slate-200">{{ $entry->description ?: $entry->type->label() }}</div>
                            <div class="mt-1 text-[10px] text-slate-400">{{ jalali_date($entry->occurred_at ?? $entry->created_at, 'Y/m/d H:i') }}</div>
                        </div>
                        <div class="shrink-0 text-left">
                            <div class="text-sm font-black {{ $credit ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}" dir="ltr">
                                {{ $credit ? '+' : '-' }}{{ number_format((int)$entry->amount) }}
                            </div>
                            <div class="mt-1 text-[10px] text-slate-400">مانده {{ number_format((int)$entry->balance_after) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-xs text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">هنوز گردش امتیازی ثبت نشده است.</div>
                @endforelse
            </div>

            @if($pointEntries->hasPages())
                <div class="mt-4">{{ $pointEntries->fragment('point-history')->links() }}</div>
            @endif
        </section>
    </div>
@endsection
