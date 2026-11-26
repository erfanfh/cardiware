@php
    $boxTitle = get_setting('products', 'first_box_title') ?: 'Latest products';
    $boxSubtitle = get_setting('products', 'first_box_subtitle');

    if(isset($category)){
        $boxTitle = $category->title;
        $categoryDescription = trim(strip_tags(html_entity_decode((string) $category->description)));
        $boxSubtitle = $categoryDescription !== ''
            ? \Illuminate\Support\Str::limit($categoryDescription, 220)
            : "Latest products in category {$category->title}";
    } elseif(isset($tag)){
        $boxTitle = $tag->title;
        $tagDescription = trim(strip_tags(html_entity_decode((string) $tag->description)));
        $boxSubtitle = $tagDescription !== ''
            ? \Illuminate\Support\Str::limit($tagDescription, 220)
            : "Latest products tagged {$tag->title}";
    }
@endphp

<div
    x-data="{ mobileFiltersOpen: false }"
    x-on:keydown.escape.window="mobileFiltersOpen = false"
    class="flex min-w-0 flex-col gap-4 sm:gap-6
    dark:text-slate-200"
>
    @include('front.catalog.products.partials.list.slider')

    @include('front.catalog.products.partials.list.title')

    {{-- Mobile Filter Button --}}
    <div class="lg:hidden">
        <button
            type="button"
            @click="mobileFiltersOpen = true"
            class="flex h-12 w-full items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-900 shadow-sm transition active:scale-[0.99]
            dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
        >
            <span class="flex items-center gap-2">
                <span
                    class="grid h-8 w-8 place-items-center rounded-xl bg-brand-50 text-brand-600 [&_svg]:h-4 [&_svg]:w-4
                    dark:bg-brand-900/30 dark:text-brand-300">
                    @include('icons.filter')
                </span>

                Product filters
            </span>

            <span class="rounded-xl bg-slate-100 px-2.5 py-1 text-[11px] font-extrabold text-slate-500
            dark:bg-slate-800 dark:text-slate-300">
                Open
            </span>
        </button>
    </div>

    {{-- Mobile Filter Popup --}}
    <div
        x-cloak
        x-show="mobileFiltersOpen"
        x-transition.opacity.duration.150ms
        class="fixed inset-0 z-[80] flex items-end justify-center lg:hidden"
        style="display: none;"
    >
        <div
            class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm dark:bg-black/80"
            @click="mobileFiltersOpen = false"
        ></div>

        <div
            x-show="mobileFiltersOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="relative max-h-[88vh] w-full overflow-hidden rounded-t-3xl border border-slate-200 bg-white shadow-2xl
            dark:border-slate-700 dark:bg-slate-900"
        >
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-4 py-4
            dark:border-slate-800">
                <div>
                    <div class="text-base font-black text-slate-900 dark:text-slate-100">
                        Product filters
                    </div>

                    <div class="mt-1 text-xs font-medium leading-6 text-slate-500 dark:text-slate-400">
                        Choose category, price and preferred options.
                    </div>
                </div>

                <button
                    type="button"
                    @click="mobileFiltersOpen = false"
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition hover:bg-slate-100
                    dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                    aria-label="Close filters"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <div class="max-h-[calc(88vh-82px)] overflow-y-auto p-4
            dark:bg-slate-900">
                @include('front.catalog.products.partials.list.filters')
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:gap-6">
        {{-- Desktop Filters --}}
        <div class="hidden lg:col-span-3 lg:block">
            @include('front.catalog.products.partials.list.filters')
        </div>

        <main class="min-w-0 lg:col-span-9">
            <x-front.cart class="mb-4 flex flex-col gap-3 p-4 sm:mb-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <h3 class="text-sm font-black text-slate-950 dark:text-slate-100">
                        Product list
                    </h3>

                    <p class="mt-1 text-xs font-medium leading-5 text-slate-400 dark:text-slate-500">
                        Sort and browse available products
                    </p>
                </div>

                <form method="GET" action="{{ $productListUrl }}"
                      class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center sm:gap-3">
                    @foreach((array) request('categories', []) as $categoryId)
                        <input type="hidden" name="categories[]" value="{{ $categoryId }}">
                    @endforeach

                    @if(request()->filled('priceMin'))
                        <input type="hidden" name="priceMin" value="{{ request('priceMin') }}">
                    @endif

                    @if(request()->filled('priceMax'))
                        <input type="hidden" name="priceMax" value="{{ request('priceMax') }}">
                    @endif

                    {{-- Glass sorting tabs --}}
                    @php
                        $sortOptions = [
                            'latest' => 'Newest',
                            'oldest' => 'Oldest',
                            'price_asc' => 'Lowest price',
                            'price_desc' => 'Highest price',
                        ];
                    @endphp

                    <div class="flex w-full flex-wrap items-center gap-1.5 rounded-2xl border border-white/60 bg-white/70 p-1.5 shadow-sm backdrop-blur-xl sm:w-auto dark:border-white/10 dark:bg-white/5 dark:shadow-black/20">
                        <span class="px-2 text-[11px] font-black text-slate-400 dark:text-slate-500">Sort</span>

                        @foreach($sortOptions as $value => $label)
                            <a href="{{ request()->fullUrlWithQuery(['sort' => $value, 'page' => 1]) }}"
                               class="inline-flex h-9 items-center rounded-xl px-4 text-xs font-extrabold transition
                               {{ $sort === $value
                                   ? 'bg-gradient-to-l from-brand-500 to-brand-400 text-white shadow-md shadow-brand-500/30'
                                   : 'text-slate-600 hover:bg-white/80 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </form>
            </x-front.cart>

            <div class="transition">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:gap-5">
                    @forelse($products as $product)
                        <x-front.product.card :product="$product"/>
                    @empty
                        <div
                            class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-12 text-center sm:px-6 sm:py-16
                            dark:border-slate-700 dark:bg-slate-900">
                            <div
                                class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-2xl sm:h-16 sm:w-16
                                dark:bg-slate-800">
                                🛒
                            </div>

                            <h3 class="mt-5 text-base font-black text-slate-950 sm:text-lg dark:text-slate-100">
                                No products match these filters
                            </h3>

                            <p class="mt-2 text-xs leading-6 text-slate-500 sm:text-sm dark:text-slate-400">
                                Change or clear the filters.
                            </p>

                            <a
                                href="{{ $productListUrl }}"
                                class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-2xl bg-brand-600 px-5 text-sm font-black text-white transition hover:bg-brand-700 sm:w-auto
                                dark:bg-brand-500 dark:hover:bg-brand-600"
                            >
                                Clear filters
                            </a>
                        </div>
                    @endforelse
                </div>

                @if($products->hasPages())
                    <div class="mt-5 sm:mt-6">
                        <x-front.list.path-pagination
                            :model="$products"
                            :base-route="$productListRoute"
                            :page-route="$productListPageRoute"
                            :route-parameters="$productListRouteParameters"
                        />
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>
