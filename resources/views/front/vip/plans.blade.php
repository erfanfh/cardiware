@extends('front.master')

@section('head')
    <title>Subscription VIP | {{ get_setting('general', 'title') }}</title>
    <meta name="description" content="Subscription plans VIP Compare plans and purchase the right one through the cart.">
@endsection

@section('content')
    <main class="overflow-hidden">
        <section class="relative isolate overflow-hidden bg-gradient-to-br from-amber-50 via-white to-orange-50 text-slate-950 dark:bg-none dark:bg-slate-950 dark:text-white">
            <div class="pointer-events-none absolute -right-28 -top-36 h-[30rem] w-[30rem] rounded-full bg-amber-300/25 blur-[100px] dark:bg-amber-500/20"></div>
            <div class="pointer-events-none absolute -bottom-52 left-0 h-[32rem] w-[32rem] rounded-full bg-brand-200/25 blur-[110px] dark:bg-brand-500/20"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.035] dark:opacity-[0.07] [background-image:radial-gradient(#475569_1px,transparent_1px)] dark:[background-image:radial-gradient(#fff_1px,transparent_1px)] [background-size:24px_24px]"></div>

            <div class="relative mx-auto max-w-7xl px-4 pb-20 pt-16 sm:px-6 sm:pb-24 sm:pt-24 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <span class="inline-flex items-center gap-2 rounded-full border border-amber-300 bg-amber-100 px-4 py-2 text-xs font-black tracking-wide text-amber-800 dark:border-amber-300/30 dark:bg-amber-300/10 dark:text-amber-200">
                        <span class="h-4 w-4 [&_svg]:h-4 [&_svg]:w-4">@include('icons.crown-03')</span>
                        {{ $hero['hero_badge'] }}
                    </span>
                    <h1 class="mt-7 text-4xl font-black leading-[1.3] sm:text-5xl lg:text-6xl">
                        {{ $hero['hero_title'] }} <span class="bg-gradient-to-l from-amber-700 via-orange-600 to-amber-500 bg-clip-text text-transparent dark:from-amber-200 dark:via-amber-400 dark:to-orange-500">{{ $hero['hero_highlight'] }}</span>
                    </h1>
                    <p class="mx-auto mt-6 max-w-2xl text-sm leading-8 text-slate-600 dark:text-slate-300 sm:text-base">
                        {{ $hero['hero_description'] }}
                    </p>
                    <a href="#vip-plans" class="mt-8 inline-flex cursor-pointer items-center gap-2 rounded-2xl bg-amber-400 px-7 py-3.5 text-sm font-black text-slate-950 shadow-xl shadow-amber-500/20 transition hover:-translate-y-0.5 hover:bg-amber-300">
                        {{ $hero['hero_button'] }} <span aria-hidden="true">↓</span>
                    </a>
                    <a href="{{ front_route('vip.products.index') }}" class="mt-4 inline-flex items-center gap-2 rounded-2xl border border-amber-300/80 bg-white/70 px-7 py-3.5 text-sm font-black text-slate-800 transition hover:bg-white dark:border-white/20 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 sm:mr-3 sm:mt-8">
                        View products VIP <span aria-hidden="true">←</span>
                    </a>
                </div>

                <div class="mx-auto mt-14 grid max-w-3xl gap-3 text-center text-xs font-bold text-slate-700 dark:text-slate-200 sm:grid-cols-3">
                    @for($number = 1; $number <= 3; $number++)
                        <div class="rounded-2xl border border-amber-200/70 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5 dark:shadow-none">{{ $hero['hero_benefit_'.$number] }}</div>
                    @endfor
                </div>
            </div>
        </section>

        <section id="vip-plans" class="relative mx-auto max-w-7xl scroll-mt-10 px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <div class="text-xs font-black tracking-widest text-amber-600 dark:text-amber-400">Choose subscription</div>
                <h2 class="mt-3 text-3xl font-black text-slate-950 dark:text-white sm:text-4xl">Which plan suits you?</h2>
                <p class="mt-4 text-sm leading-7 text-slate-500 dark:text-slate-400">All prices are in toman; validity starts upon activation.</p>
            </div>

            @if($vipExpiresAt)
                <div class="relative mx-auto mt-10 max-w-4xl overflow-hidden rounded-[2rem] border border-emerald-200/80 bg-gradient-to-br from-emerald-50 via-white to-teal-50 p-5 shadow-xl shadow-emerald-900/5 sm:p-7 dark:border-emerald-400/20 dark:from-emerald-950/70 dark:via-slate-900 dark:to-teal-950/70 dark:shadow-black/20">
                    <div class="pointer-events-none absolute -left-12 -top-20 h-44 w-44 rounded-full bg-emerald-300/20 blur-3xl dark:bg-emerald-400/10"></div>
                    <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-4">
                            <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/20 dark:bg-emerald-400 dark:text-emerald-950 [&_svg]:h-7 [&_svg]:w-7">@include('icons.crown-03')</span>
                            <div>
                                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-3 py-1 text-[11px] font-black text-emerald-700 dark:border-emerald-400/25 dark:bg-emerald-400/10 dark:text-emerald-300">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_0_3px_rgba(16,185,129,0.15)]"></span>
                                    Active status
                                </span>
                                <h3 class="mt-3 text-lg font-black text-slate-900 sm:text-xl dark:text-white">Subscription VIP is active</h3>
                                <p class="mt-2 text-xs leading-6 text-slate-600 sm:text-sm dark:text-slate-300">A new plan extends your current expiry date by its duration.</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-emerald-200/80 bg-white/80 px-5 py-4 shadow-sm sm:min-w-64 dark:border-emerald-400/20 dark:bg-white/5">
                            <div class="flex items-center gap-2 text-xs font-bold text-emerald-700 dark:text-emerald-300">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Expiry date and time
                            </div>
                            <time datetime="{{ $vipExpiresAt->toIso8601String() }}" class="mt-2 block text-base font-black leading-7 text-slate-900 sm:text-lg dark:text-white">
                                {{ (($vipExpiresAt) ? \Illuminate\Support\Carbon::parse($vipExpiresAt)->format('j F Y') : '') }} hours {{ (($vipExpiresAt) ? \Illuminate\Support\Carbon::parse($vipExpiresAt)->format('H:i') : '') }}
                            </time>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div role="alert" class="mx-auto mt-8 max-w-3xl rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-700">{{ session('error') }}</div>
            @endif

            @if($plans->isEmpty())
                <div class="mx-auto mt-12 max-w-xl rounded-3xl border border-slate-200 bg-white p-10 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-5xl">✦</div>
                    <h3 class="mt-5 text-xl font-black">No active plans yet</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-500 dark:text-slate-400">New plans will appear here soon.</p>
                </div>
            @else
                <div class="mx-auto mt-12 grid max-w-6xl gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($plans as $plan)
                        @php($isFeatured = $featuredPlanId !== null && (int) $featuredPlanId === (int) $plan->id)
                        <article class="group relative flex h-full flex-col overflow-hidden rounded-[2rem] border {{ $isFeatured ? 'border-amber-400/60 bg-slate-950 text-white shadow-2xl shadow-amber-500/15' : 'border-slate-200 bg-white text-slate-900 shadow-xl shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-900 dark:text-white' }} transition duration-300 hover:-translate-y-1 hover:shadow-2xl">
                            @if($isFeatured)
                                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-l from-amber-200 via-amber-400 to-orange-500"></div>
                            @endif
                            <div class="flex flex-1 flex-col p-7 sm:p-8">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="grid h-12 w-12 place-items-center rounded-2xl {{ $isFeatured ? 'bg-amber-400 text-slate-950' : 'bg-amber-50 text-amber-600 dark:bg-amber-400/10 dark:text-amber-300' }} [&_svg]:h-6 [&_svg]:w-6">@include('icons.crown-03')</div>
                                    @if($isFeatured)<span class="rounded-full border border-amber-300/30 bg-amber-300/10 px-3 py-1.5 text-[11px] font-black text-amber-200">Featured choice</span>@endif
                                </div>
                                <h3 class="mt-7 text-2xl font-black">{{ $plan->title }}</h3>
                                <p class="mt-3 min-h-14 whitespace-pre-line text-sm leading-7 {{ $isFeatured ? 'text-slate-300' : 'text-slate-500 dark:text-slate-400' }}">{{ $plan->description ?: 'Validity VIP For an exclusive experience in Cardiware.' }}</p>

                                <div class="mt-8 border-t {{ $isFeatured ? 'border-white/15' : 'border-slate-100 dark:border-slate-800' }} pt-7">
                                    <div class="flex items-end gap-2">
                                        <span class="text-4xl font-black tabular-nums sm:text-5xl">{{ number_format($plan->price_toman) }}</span>
                                        <span class="pb-1 text-sm font-bold {{ $isFeatured ? 'text-slate-300' : 'text-slate-500 dark:text-slate-400' }}">Toman</span>
                                    </div>
                                    <div class="mt-5 flex items-center gap-2 text-sm font-bold">
                                        <span class="grid h-6 w-6 place-items-center rounded-full {{ $isFeatured ? 'bg-amber-400/15 text-amber-300' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300' }}">✓</span>
                                        {{ number_format($plan->duration_days) }} days of validity VIP
                                    </div>
                                    <div class="mt-3 flex items-center gap-2 text-sm font-bold">
                                        <span class="grid h-6 w-6 place-items-center rounded-full {{ $isFeatured ? 'bg-amber-400/15 text-amber-300' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300' }}">✓</span>
                                        Automatic extension on repurchase
                                    </div>
                                </div>

                                @auth
                                    <form method="post" action="{{ route('vip.cart.add', $plan) }}" class="mt-auto pt-9">
                                        @csrf
                                        <button type="submit" class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl px-5 py-3.5 text-sm font-black transition {{ $isFeatured ? 'bg-amber-400 text-slate-950 hover:bg-amber-300' : 'bg-slate-950 text-white hover:bg-brand-600 dark:bg-white dark:text-slate-950 dark:hover:bg-amber-300' }}">
                                            Select plan and continue <span aria-hidden="true">←</span>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('auth.index') }}" class="mt-auto inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl px-5 py-3.5 text-sm font-black transition {{ $isFeatured ? 'bg-amber-400 text-slate-950 hover:bg-amber-300' : 'bg-slate-950 text-white hover:bg-brand-600 dark:bg-white dark:text-slate-950 dark:hover:bg-amber-300' }}">
                                        Sign in and select a plan <span aria-hidden="true">←</span>
                                    </a>
                                @endauth
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            <p class="mx-auto mt-10 max-w-3xl text-center text-xs leading-7 text-slate-500 dark:text-slate-400">Subscription activates after payment confirmation. Purchase details appear in your account orders.</p>
        </section>
    </main>
@endsection
