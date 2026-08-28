@if(isset($loyaltyRaffleRewardOffers) && $loyaltyRaffleRewardOffers->isNotEmpty())
    <section id="loyalty-tickets" class="scroll-mt-24">
        <x-front.cart class="relative overflow-hidden !p-0">
            <div class="absolute inset-0 bg-gradient-to-l from-amber-50/80 via-white to-brand-50/60 dark:from-amber-950/10 dark:via-slate-900 dark:to-brand-950/20"></div>
            <div class="absolute -left-14 -top-14 h-36 w-36 rounded-full bg-amber-200/40 blur-3xl dark:bg-amber-500/10"></div>

            <div class="relative p-4 sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div class="text-[10px] font-extrabold text-amber-600 dark:text-amber-300">باشگاه مخاطبان</div>
                        <h2 class="mt-1 text-base font-black text-slate-900 dark:text-white sm:text-lg">با امتیازت بلیط بگیر</h2>
                        <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">برای این قرعه کشی پاداش امتیازی فعال وجود دارد. بلیط ها بعد از دریافت مستقیما به نام حساب تو صادر می شوند.</p>
                    </div>
                    @auth
                        <a href="{{ route('account.loyalty') }}#rewards" class="text-[11px] font-extrabold text-brand-600 dark:text-brand-300">مشاهده باشگاه مخاطبان</a>
                    @endauth
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach($loyaltyRaffleRewardOffers as $item)
                        @php
                            $offer = $item['offer'];
                            $availability = $item['availability'];
                            $quantity = (int) data_get($offer->config, 'ticket_quantity', 1);
                        @endphp
                        <div class="rounded-2xl border border-white/80 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-950/60">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-black leading-6 text-slate-900 dark:text-white">{{ $offer->title }}</div>
                                    @if($offer->description)
                                        <p class="mt-1 line-clamp-2 text-[11px] leading-6 text-slate-500 dark:text-slate-400">{{ $offer->description }}</p>
                                    @endif
                                </div>
                                <div class="shrink-0 rounded-xl bg-amber-50 px-3 py-2 text-center dark:bg-amber-500/10">
                                    <div class="text-base font-black text-amber-700 dark:text-amber-300">{{ number_format($quantity) }}</div>
                                    <div class="text-[9px] font-bold text-amber-500">بلیط</div>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-900">
                                <span class="text-[10px] font-bold text-slate-400">هزینه دریافت</span>
                                <span class="text-xs font-black text-brand-700 dark:text-brand-300">{{ number_format($offer->points_cost) }} امتیاز</span>
                            </div>

                            <div class="mt-3">
                                @guest
                                    <a href="{{ route('auth.index') }}" class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-brand-500 px-4 text-[11px] font-extrabold text-white transition hover:bg-brand-600 dark:bg-brand-600 dark:hover:bg-brand-500">
                                        ورود و دریافت بلیط
                                    </a>
                                @else
                                    @if($availability?->canRedeem)
                                        <form method="post" action="{{ route('account.loyalty.rewards.redeem', $offer) }}">
                                            @csrf
                                            <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                                            <button type="submit" class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-brand-500 px-4 text-[11px] font-extrabold text-white transition hover:bg-brand-600 active:bg-brand-700 dark:bg-brand-600 dark:hover:bg-brand-500">
                                                دریافت {{ number_format($quantity) }} بلیط
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" disabled class="inline-flex h-10 w-full cursor-not-allowed items-center justify-center rounded-xl bg-slate-100 px-4 text-[11px] font-extrabold text-slate-400 dark:bg-slate-800 dark:text-slate-500">فعلا قابل دریافت نیست</button>
                                        @if($availability?->reason)
                                            <div class="mt-2 text-[10px] leading-5 text-amber-700 dark:text-amber-300">{{ $availability->reason }}</div>
                                        @endif
                                    @endif
                                @endguest
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-front.cart>
    </section>
@endif
