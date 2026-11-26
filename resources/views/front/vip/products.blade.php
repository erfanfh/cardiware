@extends('front.master')

@section('head')
    <title>Accessible products VIP | {{ get_setting('general', 'title') }}</title>
    <meta name="description" content="With subscription VIP Access selected files for these products.">
@endsection

@section('content')
    <main class="overflow-hidden">
        <section class="relative isolate overflow-hidden bg-gradient-to-br from-amber-50 via-white to-orange-50 text-slate-950 dark:bg-none dark:bg-slate-950 dark:text-white">
            <div class="pointer-events-none absolute -right-28 -top-36 h-[30rem] w-[30rem] rounded-full bg-amber-300/25 blur-[100px] dark:bg-amber-500/20"></div>
            <div class="pointer-events-none absolute -bottom-52 left-0 h-[32rem] w-[32rem] rounded-full bg-brand-200/25 blur-[110px] dark:bg-brand-500/20"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.035] dark:opacity-[0.07] [background-image:radial-gradient(#475569_1px,transparent_1px)] dark:[background-image:radial-gradient(#fff_1px,transparent_1px)] [background-size:24px_24px]"></div>

            <div class="relative mx-auto max-w-7xl px-4 pb-16 pt-14 sm:px-6 sm:pb-20 sm:pt-20 lg:px-8">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-amber-300 bg-amber-100 px-4 py-2 text-xs font-black text-amber-800 dark:border-amber-300/30 dark:bg-amber-300/10 dark:text-amber-200">
                        <span class="h-4 w-4 [&_svg]:h-4 [&_svg]:w-4">@include('icons.crown-03')</span>
                        Exclusive collection of VIP
                    </span>
                    <h1 class="mt-7 text-4xl font-black leading-[1.3] sm:text-5xl lg:text-6xl">
                        Products <span class="bg-gradient-to-l from-amber-700 via-orange-600 to-amber-500 bg-clip-text text-transparent dark:from-amber-200 dark:via-amber-400 dark:to-orange-500">With access VIP</span>
                    </h1>
                    <p class="mt-5 max-w-2xl text-sm leading-8 text-slate-600 dark:text-slate-300 sm:text-base">
                        With subscription VIP Access selected files for these products.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <a href="#vip-products" class="inline-flex items-center gap-2 rounded-2xl bg-amber-400 px-6 py-3.5 text-sm font-black text-slate-950 shadow-xl shadow-amber-500/20 transition hover:-translate-y-0.5 hover:bg-amber-300">
                            View products <span aria-hidden="true">↓</span>
                        </a>
                        <a href="{{ front_route('vip.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-amber-300/80 bg-white/70 px-6 py-3.5 text-sm font-black text-slate-800 transition hover:bg-white dark:border-white/20 dark:bg-white/5 dark:text-white dark:hover:bg-white/10">
                            About subscription VIP <span aria-hidden="true">←</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section id="vip-products" class="mx-auto max-w-7xl scroll-mt-8 px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <div class="mb-7 flex flex-col gap-5 sm:mb-9 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-xs font-black tracking-widest text-amber-600 dark:text-amber-400">Catalog VIP</div>
                    <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white sm:text-3xl">Products accessible with VIP</h2>
                </div>
                @if($products->isNotEmpty())
                    <form method="get" action="{{ front_route('vip.products.index') }}" class="flex items-center gap-3">
                        <label for="vip-product-sort" class="shrink-0 text-xs font-black text-slate-500 dark:text-slate-400">Sort:</label>
                        <select id="vip-product-sort" name="sort" onchange="this.form.submit()" class="h-11 cursor-pointer rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:focus:ring-amber-400/10">
                            <option value="latest" @selected($sort === 'latest')>Newest</option>
                            <option value="oldest" @selected($sort === 'oldest')>Oldest</option>
                        </select>
                    </form>
                @endif
            </div>

            @if($products->isEmpty())
                <div class="rounded-[2rem] border border-dashed border-amber-300/70 bg-white px-6 py-16 text-center shadow-sm dark:border-amber-400/20 dark:bg-slate-900">
                    <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-400/10 dark:text-amber-300 [&_svg]:h-8 [&_svg]:w-8">@include('icons.crown-03')</span>
                    <h3 class="mt-5 text-xl font-black text-slate-950 dark:text-white">No products VIP have been published yet</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-500 dark:text-slate-400">New products will appear after access is enabled VIP on this page.</p>
                    <a href="{{ front_route('products.list') }}" class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-brand-600 px-5 py-3 text-sm font-black text-white transition hover:bg-brand-700">View all products <span aria-hidden="true">←</span></a>
                </div>
            @else
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($products as $product)
                        <div class="relative min-w-0">
                            <x-front.product.card :product="$product" />
                            <span class="pointer-events-none absolute right-3 top-3 inline-flex items-center gap-1.5 rounded-full border border-amber-300/50 bg-slate-950/85 px-3 py-1.5 text-[11px] font-black text-amber-200 shadow-lg backdrop-blur-sm [&_svg]:h-3.5 [&_svg]:w-3.5">
                                @include('icons.crown-03') Access VIP
                            </span>
                        </div>
                    @endforeach
                </div>

                @if($products->hasPages())
                    <div class="mt-9 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900 sm:p-5">
                        <x-front.list.path-pagination :model="$products" base-route="vip.products.index" page-route="vip.products.page" />
                    </div>
                @endif
            @endif
        </section>
    </main>
@endsection
