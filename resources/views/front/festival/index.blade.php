@extends('front.master')

@section('title', $festival['title'])

@section('content')
    @php
        $accent = $festival['themeColor'];
        $accentSoft = $accent . '24'; // 14% Alpha
        $hasCountdown = $festival['countdownEnabled'] && filled($festival['countdownEndsAt']) && strtotime($festival['countdownEndsAt']) > time();
    @endphp

    <main class="mw-festival relative isolate overflow-hidden" style="--festival-accent: {{ $accent }}; --festival-accent-soft: {{ $accentSoft }};">
        {{-- Optional background + Pattern --}}
        @if($festival['bgImage'])
            <img src="{{ $festival['bgImage'] }}" alt="" class="mw-festival-bg" loading="lazy">
        @endif

        @if(in_array($festival['bgPattern'], ['dots', 'grid', 'diag'], true))
            <span class="mw-footer-pattern is-{{ $festival['bgPattern'] }} mw-festival-pattern" aria-hidden="true"></span>
        @endif

        {{-- Festival header --}}
        <section class="mw-festival-hero relative">
            <div class="mx-auto max-w-6xl px-4 py-14 sm:py-20">
                <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2">
                    <div class="text-center lg:text-start">
                        @if($festival['badge'] !== '')
                            <span class="mw-rise d-1 inline-flex items-center gap-2 rounded-2xl border px-4 py-2 text-xs font-black backdrop-blur"
                                  style="border-color: var(--festival-accent); background: var(--festival-accent-soft); color: var(--festival-accent);">
                                <span class="h-2 w-2 animate-pulse rounded-full" style="background: var(--festival-accent);"></span>
                                {{ $festival['badge'] }}
                            </span>
                        @endif

                        <h1 class="mw-rise d-2 mt-4 text-3xl font-black leading-tight text-slate-950 sm:text-5xl dark:text-white">
                            {{ $festival['title'] }}
                        </h1>

                        @if($festival['subtitle'] !== '')
                            <p class="mw-rise d-2 mt-3 text-base font-extrabold sm:text-xl" style="color: var(--festival-accent);">
                                {{ $festival['subtitle'] }}
                            </p>
                        @endif

                        @if($festival['description'] !== '')
                            <p class="mw-rise d-3 mx-auto mt-4 max-w-xl whitespace-pre-line text-sm leading-8 text-slate-600 lg:mx-0 dark:text-slate-300">
                                {{ $festival['description'] }}
                            </p>
                        @endif

                        @if($festival['discountText'] !== '')
                            <div class="mw-rise d-3 mt-6 inline-flex items-stretch overflow-hidden rounded-2xl shadow-xl"
                                 style="box-shadow: 0 14px 34px -12px var(--festival-accent);">
                                <span class="grid place-items-center px-5 py-3 text-sm font-black text-white" style="background: var(--festival-accent);">until</span>
                                <span class="grid place-items-center bg-white px-6 py-3 text-2xl font-black text-slate-950 dark:bg-slate-900 dark:text-white">
                                    {{ $festival['discountText'] }}
                                </span>
                            </div>
                        @endif

                        {{-- Countdown --}}
                        @if($hasCountdown)
                            <div class="mw-rise d-4 mt-7 flex items-center justify-center gap-2.5 lg:justify-start"
                                 x-data="{
                                    ends: @js(strtotime($festival['countdownEndsAt']) * 1000),
                                    remaining: 0,
                                    tick() {
                                        this.remaining = Math.max(0, Math.floor((this.ends - Date.now()) / 1000));
                                        if (this.remaining > 0) setTimeout(() => this.tick(), 1000);
                                    },
                                    get parts() {
                                        const s = this.remaining;
                                        return {
                                            d: String(Math.floor(s / 86400)).padStart(2, '0'),
                                            h: String(Math.floor((s % 86400) / 3600)).padStart(2, '0'),
                                            m: String(Math.floor((s % 3600) / 60)).padStart(2, '0'),
                                            s: String(s % 60).padStart(2, '0'),
                                        };
                                    }
                                 }"
                                 x-init="tick()">
                                <template x-for="(label, key) in {d: 'days', h: 'hours', m: 'minutes', s: 'seconds'}" :key="key">
                                    <div class="grid w-[4.4rem] place-items-center rounded-2xl border bg-white/80 py-2.5 backdrop-blur-xl dark:bg-slate-900/70"
                                         style="border-color: var(--festival-accent-soft);">
                                        <b class="text-xl font-black tabular-nums text-slate-950 dark:text-white" x-text="parts[key]">00</b>
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400" x-text="label"></span>
                                    </div>
                                </template>
                            </div>
                        @endif

                        {{-- Discount code --}}
                        @if($festival['couponCode'] !== '')
                            <div class="mw-rise d-4 mt-7" x-data="{ copied: false }">
                                <div class="inline-flex flex-wrap items-center gap-3 rounded-2xl border border-dashed p-2.5 pl-4"
                                     style="border-color: var(--festival-accent); background: var(--festival-accent-soft);">
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Discount code:</span>

                                    <code class="rounded-xl bg-white px-3 py-1.5 text-sm font-black tracking-widest text-slate-950 dark:bg-slate-900 dark:text-white" dir="ltr">{{ $festival['couponCode'] }}</code>

                                    <button type="button"
                                            class="cursor-pointer rounded-xl px-3.5 py-1.5 text-xs font-extrabold text-white transition active:scale-95"
                                            style="background: var(--festival-accent);"
                                            @click="navigator.clipboard?.writeText(@js($festival['couponCode'])).then(() => { copied = true; setTimeout(() => copied = false, 1800) })">
                                        <span x-text="copied ? 'Copied ✓' : 'Copy code'"></span>
                                    </button>
                                </div>

                                @if($festival['couponHint'] !== '')
                                    <p class="mt-2 text-[11.5px] font-semibold text-slate-500 dark:text-slate-400">{{ $festival['couponHint'] }}</p>
                                @endif
                            </div>
                        @endif

                        @if($festival['buttonText'] !== '')
                            <div class="mw-rise d-5 mt-8">
                                <a href="{{ $festival['buttonUrl'] ?: '#' }}"
                                   class="mw-festival-cta inline-flex items-center gap-2 rounded-2xl px-7 py-3.5 text-sm font-black text-white shadow-xl transition hover:-translate-y-0.5 active:translate-y-0"
                                   style="background: var(--festival-accent); box-shadow: 0 16px 34px -12px var(--festival-accent);">
                                    {{ $festival['buttonText'] }}
                                    <span class="[&_svg]:h-4 [&_svg]:w-4">
                                        <x-front.arrow-right-02/>
                                    </span>
                                </a>
                            </div>
                        @endif
                    </div>

                    @if($festival['heroImage'])
                        <div class="mw-rise d-3 relative mx-auto max-w-md lg:max-w-none">
                            <div class="absolute -inset-6 rounded-[2.5rem] opacity-25 blur-2xl" style="background: var(--festival-accent);" aria-hidden="true"></div>
                            <img src="{{ $festival['heroImage'] }}" alt="{{ $festival['title'] }}"
                                 class="relative w-full rounded-[2rem] object-cover shadow-2xl ring-1 ring-white/40 dark:ring-white/10"
                                 loading="eager">
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- Festival offers --}}
        @if($festival['deals']->isNotEmpty())
            <section class="relative mx-auto max-w-6xl px-4 pb-16">
                <h2 class="text-xl font-black text-slate-950 sm:text-2xl dark:text-white">
                    {{ $festival['dealsTitle'] }}
                </h2>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($festival['deals'] as $item)
                        <a href="{{ $item['link'] ?? '#' }}"
                           class="group relative flex flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1.5 hover:shadow-2xl dark:bg-slate-900/70 dark:ring-slate-800"
                           style="--tw-shadow-color: var(--festival-accent);">
                            <div class="relative aspect-[16/10] overflow-hidden bg-slate-100 dark:bg-slate-800">
                                @if(!empty($item['image']['path']))
                                    <img src="{{ $item['image']['path'] }}" alt="{{ $item['title'] }}"
                                         class="h-full w-full object-cover transition duration-700 group-hover:scale-[1.06]" loading="lazy">
                                @endif

                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 to-transparent"></div>

                                @if(filled($item['badge'] ?? null))
                                    <span class="absolute top-2.5 right-2.5 rounded-xl px-2.5 py-1 text-[10.5px] font-black text-white shadow-lg ring-1 ring-white/25 backdrop-blur-sm"
                                          style="background: var(--festival-accent);">
                                        {{ $item['badge'] }}
                                    </span>
                                @endif

                                @if(filled($item['price_text'] ?? null))
                                    <span class="absolute bottom-2.5 right-2.5 inline-flex items-center gap-1.5 rounded-xl border border-white/20 bg-slate-950/55 px-2.5 py-1 text-[11.5px] font-black text-white backdrop-blur-md">
                                        {{ $item['price_text'] }}
                                        @if(filled($item['old_price_text'] ?? null))
                                            <s class="text-[9.5px] font-bold text-white/55">{{ $item['old_price_text'] }}</s>
                                        @endif
                                    </span>
                                @endif
                            </div>

                            <div class="flex flex-1 flex-col p-4">
                                <div class="line-clamp-2 min-h-[3.25rem] text-sm font-extrabold leading-7 text-slate-900 transition group-hover:text-slate-950 dark:text-slate-100">
                                    {{ $item['title'] }}
                                </div>

                                @if(filled($item['short_description'] ?? null))
                                    <p class="mt-1.5 line-clamp-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                        {{ $item['short_description'] }}
                                    </p>
                                @endif

                                <div class="mt-auto pt-4">
                                    <span class="inline-flex w-full items-center justify-center rounded-2xl px-3.5 py-2.5 text-xs font-extrabold text-white transition group-hover:brightness-110"
                                          style="background: var(--festival-accent);">
                                        {{ $item['button_text'] ?? 'View and buy' }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($festival['footerNote'] !== '')
            <section class="relative mx-auto max-w-3xl px-4 pb-14 text-center">
                <p class="whitespace-pre-line text-xs leading-7 text-slate-500 dark:text-slate-400">
                    {{ $festival['footerNote'] }}
                </p>
            </section>
        @endif
    </main>
@endsection
