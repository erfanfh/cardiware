<div role="region" aria-label="Special offer">
    @if($promotion)
        @php($isActive = $promotion['state'] === 'active')
        @php($isFinished = $promotion['state'] === 'finished')

        <aside
            data-purchase-notification-blocker
            wire:poll.visible.20s
            wire:key="floating-coupon-{{ $promotion['id'] }}-{{ $promotion['state'] }}"
            x-data="{
                open: localStorage.getItem('floating-coupon-dismissed') !== @js((string) $promotion['id']),
                copied: false,
                dismiss() {
                    this.open = false;
                    localStorage.setItem('floating-coupon-dismissed', @js((string) $promotion['id']));
                },
                copyCode() {
                    navigator.clipboard?.writeText(@js($promotion['code']));
                    this.copied = true;
                    setTimeout(() => this.copied = false, 1800);
                }
            }"
            x-show="open"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="translate-y-8 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-250"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="translate-y-8 opacity-0"
            x-cloak
            class="pointer-events-none fixed inset-x-3 bottom-3 z-[99980] mx-auto max-w-5xl sm:bottom-5"
            aria-live="polite"
            dir="ltr"
        >
            <div class="pointer-events-auto relative overflow-hidden rounded-[1.65rem] border border-orange-200/80 bg-white/95 shadow-[0_24px_70px_rgba(124,45,18,0.22)] backdrop-blur-xl dark:border-orange-400/20 dark:bg-slate-900/95 dark:shadow-[0_28px_80px_rgba(0,0,0,0.58)]">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="absolute -right-16 -top-24 h-52 w-52 rounded-full bg-orange-300/25 blur-3xl dark:bg-orange-400/15"></div>
                    <div class="absolute -bottom-24 left-1/3 h-44 w-44 rounded-full bg-amber-200/30 blur-3xl dark:bg-amber-300/10"></div>
                    <div class="absolute inset-y-0 right-0 w-1 bg-gradient-to-b from-orange-400 via-amber-400 to-rose-400"></div>
                </div>

                <div class="relative flex flex-col gap-3 p-3.5 sm:flex-row sm:items-center sm:gap-4 sm:p-4 lg:px-5">
                    <div class="flex min-w-0 flex-1 items-start gap-3 sm:items-center">
                        <div class="relative grid h-12 w-12 shrink-0 place-items-center rounded-2xl text-white shadow-lg sm:h-14 sm:w-14 [&_svg]:h-6 [&_svg]:w-6 {{ $isFinished ? 'bg-gradient-to-br from-slate-400 to-slate-600 shadow-slate-500/20' : 'bg-gradient-to-br from-orange-400 via-orange-500 to-rose-500 shadow-orange-500/30' }}">
                            @include($isFinished ? 'icons.checkmark-badge-01' : 'icons.fire')
                            @if($isActive)
                                <span class="absolute -left-1 -top-1 h-3 w-3 rounded-full border-2 border-white bg-emerald-400 dark:border-slate-900">
                                    <span class="absolute inset-0 animate-ping rounded-full bg-emerald-400"></span>
                                </span>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-black ring-1 ring-inset {{ $isFinished ? 'bg-slate-100 text-slate-600 ring-slate-200 dark:bg-slate-700/60 dark:text-slate-300 dark:ring-slate-600' : 'bg-orange-50 text-orange-700 ring-orange-200 dark:bg-orange-400/10 dark:text-orange-300 dark:ring-orange-400/20' }}">
                                    {{ $isFinished ? ($promotion['finish_reason'] === 'sold_out' ? 'Capacity reached' : 'Offer expired') : $promotion['discount_label'] }}
                                </span>

                                @if($isActive)
                                    <span class="text-[10px] font-extrabold text-emerald-600 dark:text-emerald-400">Active now</span>
                                @elseif($promotion['state'] === 'upcoming')
                                    <span class="text-[10px] font-extrabold text-sky-600 dark:text-sky-400">Activating soon</span>
                                @elseif($promotion['state'] === 'inactive')
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400">Currently inactive</span>
                                @endif
                            </div>

                            <h2 class="mt-1.5 truncate text-sm font-black leading-6 text-slate-950 sm:text-base dark:text-white">
                                @if($isFinished)
                                    Discount «{{ $promotion['title'] }}» Ended
                                @else
                                    {{ $promotion['title'] }}
                                @endif
                            </h2>

                            <p class="mt-0.5 line-clamp-1 text-[11px] font-semibold leading-5 text-slate-500 sm:text-xs dark:text-slate-400">
                                @if($isFinished)
                                    @if($promotion['finish_reason'] === 'sold_out')
                                        All opportunities for this offer have been used; stay tuned for the next discount.
                                    @else
                                        This special offer has expired; stay tuned for the next one.
                                    @endif
                                @elseif($promotion['state'] === 'upcoming')
                                    This offer will be {{ (($promotion['starts_at']) ? \Illuminate\Support\Carbon::parse($promotion['starts_at'])->format('Y/m/d H:i') : '') }} available from.
                                @elseif($promotion['state'] === 'inactive')
                                    This offer is currently unavailable.
                                @else
                                    @if($promotion['product_title'])
                                        for «{{ $promotion['product_title'] }}»
                                    @endif
                                    Only {{ number_format($promotion['remaining']) }} opportunities out of {{ number_format($promotion['usage_limit']) }} opportunities left.
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($isActive)
                        <div class="flex items-center gap-2 sm:shrink-0">
                            <div class="hidden w-28 lg:block">
                                <div class="mb-1 flex items-center justify-between text-[9px] font-bold text-slate-400 dark:text-slate-500">
                                    <span>Remaining capacity</span>
                                    <span>{{ number_format($promotion['remaining']) }}</span>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                                    <div class="h-full rounded-full bg-gradient-to-l from-orange-400 to-rose-500 transition-all duration-700" style="width: {{ $promotion['progress'] }}%"></div>
                                </div>
                            </div>

                            <button
                                type="button"
                                x-on:click="copyCode()"
                                class="group flex h-11 min-w-0 flex-1 cursor-pointer items-center justify-between gap-3 rounded-xl border border-dashed border-orange-200 bg-orange-50/80 px-3 text-start transition hover:border-orange-300 hover:bg-orange-100/70 sm:w-32 sm:flex-none dark:border-orange-400/20 dark:bg-orange-400/10 dark:hover:bg-orange-400/15"
                                aria-label="Copy discount code {{ $promotion['code'] }}"
                            >
                                <span class="min-w-0">
                                    <span class="block text-[9px] font-bold text-orange-600 dark:text-orange-300" x-text="copied ? 'Copied ✓' : 'Discount code'"></span>
                                    <code class="block truncate text-xs font-black tracking-wide text-slate-900 dark:text-white" dir="ltr">{{ $promotion['code'] }}</code>
                                </span>
                            </button>

                            <a
                                href="{{ $promotion['url'] }}"
                                class="inline-flex h-11 shrink-0 items-center justify-center gap-1.5 rounded-xl bg-slate-950 px-4 text-xs font-black text-white shadow-lg shadow-slate-950/15 transition hover:-translate-y-0.5 hover:bg-orange-600 focus:outline-none focus-visible:ring-4 focus-visible:ring-orange-400/25 dark:bg-orange-500 dark:hover:bg-orange-400 sm:px-5 [&_svg]:h-3.5 [&_svg]:w-3.5"
                            >
                                Buy at discount
                                <x-front.arrow-right-02/>
                            </a>
                        </div>
                    @endif

                    <button
                        type="button"
                        x-on:click="dismiss()"
                        class="absolute left-2 top-2 grid h-8 w-8 cursor-pointer place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-400 dark:hover:bg-slate-800 dark:hover:text-white sm:static sm:shrink-0 [&_svg]:h-4 [&_svg]:w-4"
                        aria-label="Close discount box"
                    >
                        @include('icons.cancel-01')
                    </button>
                </div>
            </div>
        </aside>
    @endif
</div>
