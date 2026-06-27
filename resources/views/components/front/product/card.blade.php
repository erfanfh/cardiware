@props([
    'product'
])

@php
    $minPrice = $product->min_price ?? $product->saleItems->min('base_price');
    $maxPrice = $product->max_price ?? $product->saleItems->max('base_price');
    $categories = $product->categories->take(2);
    $storeTitle = $product->store?->title ?? 'فروشنده مودینگ وی';
@endphp

<a
    href="{{ front_route('products.detail', $product->slug) }}"
    class="group relative block h-full rounded-2xl outline-none transition duration-300 hover:-translate-y-1 focus-visible:ring-4 focus-visible:ring-brand-100
    dark:focus-visible:ring-brand-900/40"
>
    <article
        class="relative flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300
        group-hover:border-brand-200 group-hover:shadow-2xl group-hover:shadow-brand-100/60
        dark:border-slate-800 dark:bg-slate-900/40 dark:group-hover:border-brand-700 dark:group-hover:shadow-brand-900/20"
    >

        {{-- Image --}}
        <div class="relative overflow-hidden bg-slate-100 dark:bg-slate-800">
            @if($product->picture)
                @php
                    $w = $product->picture?->primaryVariant?->width;
                    $h = $product->picture?->primaryVariant?->height;

                    $ratio = ($w && $h) ? "{$w} / {$h}" : "4 / 3";
                @endphp

                <div
                    class="w-full overflow-hidden group"
                    style="aspect-ratio: {{ $ratio }};"
                >
                    <x-front.responsive-image
                        :media="$product->picture"
                        :fallback="$product->picture->url"
                        :alt="$product->title"
                        :title="$product->title"
                        variant="medium"
                        class="h-full w-full object-cover transition-transform duration-700"
                        :lazy="true"
                        :async="true"
                        :skeleton="true"
                        fetchpriority="high"
                        sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
                    />
                </div>
            @else
                <div
                    class="grid h-full w-full place-items-center bg-gradient-to-br from-slate-100 to-slate-200
                    dark:from-slate-800 dark:to-slate-900"
                >
                    <svg class="h-14 w-14 text-slate-300 dark:text-slate-600" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M4 7.5C4 5.57 5.57 4 7.5 4h9C18.43 4 20 5.57 20 7.5v9c0 1.93-1.57 3.5-3.5 3.5h-9C5.57 20 4 18.43 4 16.5v-9Z"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />
                        <path
                            d="M8 14l2.2-2.2a1 1 0 0 1 1.4 0L14 14.2l1.2-1.2a1 1 0 0 1 1.4 0L20 16.4"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />
                        <path
                            d="M8.5 8.5h.01"
                            stroke="currentColor"
                            stroke-width="2.5"
                            stroke-linecap="round"
                        />
                    </svg>
                </div>
            @endif

            <div
                class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/10 to-transparent opacity-80 transition group-hover:opacity-95"></div>

            {{-- Top badges --}}
            <div class="absolute inset-x-3 top-3 flex items-start justify-between gap-2">
                <div class="flex min-w-0 flex-wrap gap-1.5">
                    @foreach($categories as $category)
                        <span
                            class="max-w-[120px] truncate rounded-2xl border border-white/20 bg-white/90 px-2.5 py-1 text-[11px] font-black text-slate-700 backdrop-blur
                            dark:bg-slate-900/60 dark:text-slate-200 dark:border-slate-700"
                        >
                            {{ $category->title }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="flex flex-1 flex-col p-4 dark:text-slate-100">

            {{-- Store --}}
            <div class="mb-3 flex min-w-0 items-center gap-2 text-[11px] font-black text-brand-600 dark:text-brand-300">
                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>

                <span class="truncate">
                    {{ $storeTitle }}
                </span>
            </div>

            <h3 class="line-clamp-2 min-h-[56px] text-base font-black leading-7 text-slate-950 transition group-hover:text-brand-700
            dark:text-slate-100 dark:group-hover:text-brand-300">
                {{ $product->title }}
            </h3>

            <p class="mt-2 line-clamp-2 min-h-[48px] text-xs leading-6 text-slate-500 dark:text-slate-400">
                {{ $product->excerpt() }}
            </p>

            <div class="mt-auto pt-4">

                <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-3 py-3 transition group-hover:bg-brand-50/70
                dark:bg-slate-800 dark:group-hover:bg-brand-900/20">

                    <div class="min-w-0">

                        <span class="mb-1 block text-[11px] font-bold text-slate-400 dark:text-slate-500">
                            قیمت محصول
                        </span>

                        <div class="text-sm font-black text-slate-950 dark:text-slate-100">

                            @if($minPrice)
                                @if((int) $minPrice === (int) $maxPrice)
                                    {{ number_format($minPrice) }}
                                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">تومان</span>
                                @else
                                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">از</span>
                                    {{ number_format($minPrice) }}
                                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">تومان</span>
                                @endif
                            @else
                                <span class="text-slate-400 dark:text-slate-500">نامشخص</span>
                            @endif

                        </div>

                    </div>

                    <div
                        class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-200 transition group-hover:bg-brand-600 group-hover:text-white group-hover:ring-brand-600
                        dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-700"
                    >
                        @include('icons.arrow-left-01')
                    </div>

                </div>

            </div>

        </div>

        <div class="pointer-events-none absolute inset-0 rounded-2xl ring-1 ring-transparent transition group-hover:ring-brand-200
        dark:group-hover:ring-brand-900/40"></div>

    </article>
</a>
