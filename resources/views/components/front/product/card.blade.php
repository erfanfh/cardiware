@props([
    'product'
])

@php
    $categories = $product->categories->take(2);
    $storeTitle = $product->store?->title ?? 'فروشنده Cardiware';

    $saleItems = $product->relationLoaded('saleItems')
        ? $product->saleItems
        : $product->saleItems()->with('discount')->get();

    $availableSaleItems = $saleItems
        ->filter(function ($saleItem): bool {
            $status = $saleItem->status;
            $statusValue = $status instanceof \BackedEnum ? $status->value : $status;

            return $statusValue === 'available';
        })
        ->values();

    $priceResolver = app(\App\Application\Commerce\Actions\Pricing\ResolvePurchasablePriceAction::class);

    $priceRows = $availableSaleItems
        ->map(function ($saleItem) use ($priceResolver): array {
            $price = $priceResolver->execute($saleItem);

            $discountPercent = $price->basePrice > 0 && $price->unitDiscountAmount > 0
                ? (int) round(($price->unitDiscountAmount / $price->basePrice) * 100)
                : 0;

            return [
                'regular_price' => $price->basePrice,
                'final_price' => $price->finalPrice,
                'has_discount' => $price->hasDiscount(),
                'discount_percent' => $discountPercent,
            ];
        })
        ->filter(fn (array $row): bool => $row['final_price'] !== null)
        ->values();

    $displayPriceRow = $priceRows
        ->sortBy('final_price')
        ->first();

    $minPrice = $priceRows->min('final_price');
    $maxPrice = $priceRows->max('final_price');

    $isPriceRange = $minPrice !== null
        && $maxPrice !== null
        && (int) $minPrice !== (int) $maxPrice;

    $isFreeForDisplay = $minPrice !== null && (int) $minPrice === 0;

    $hasDiscountForDisplay = $displayPriceRow
        && $displayPriceRow['has_discount']
        && (int) $displayPriceRow['regular_price'] > (int) $displayPriceRow['final_price'];

    $regularPriceForDisplay = $displayPriceRow['regular_price'] ?? null;
    $discountPercentForDisplay = $displayPriceRow['discount_percent'] ?? 0;
@endphp

<a
    href="{{ front_route('products.detail', $product->slug) }}"
    class="group relative z-0 block h-full rounded-2xl outline-none transition duration-300 hover:-translate-y-1 hover:z-10 focus-visible:ring-4 focus-visible:ring-brand-100
    dark:focus-visible:ring-brand-900/40"
>
    <article
        class="relative flex h-full flex-col overflow-hidden rounded-2xl border border-transparent bg-white
        shadow-[0_2px_10px_rgba(15,23,42,.05)] ring-1 ring-slate-200/60 transition duration-300
        group-hover:-translate-y-1 group-hover:shadow-[0_18px_40px_-12px_rgba(15,23,42,.18)] group-hover:ring-brand-300/70
        dark:bg-slate-900/70 dark:ring-slate-800/80 dark:group-hover:shadow-[0_18px_40px_-12px_rgba(0,0,0,.5)] dark:group-hover:ring-brand-700/60"
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

                        <div class="mb-1 flex items-center gap-1.5">
                            <span class="block text-[11px] font-bold text-slate-400 dark:text-slate-500">
                                قیمت محصول
                            </span>

                            @if($hasDiscountForDisplay && $discountPercentForDisplay > 0)
                                <span
                                    class="rounded-full bg-brand-50 px-1.5 py-0.5 text-[9px] font-black text-brand-600 ring-1 ring-brand-100 dark:bg-brand-900/30 dark:text-brand-300 dark:ring-brand-900/40"
                                >
                                    {{ number_format($discountPercentForDisplay) }}٪
                                </span>
                            @endif
                        </div>

                        @if($minPrice !== null)
                            @if($hasDiscountForDisplay)
                                <div
                                    class="mb-0.5 whitespace-nowrap text-[11px] font-bold text-slate-400 line-through decoration-brand-400/70 decoration-2 dark:text-slate-500"
                                >
                                    @if($isPriceRange)
                                        <span>از</span>
                                    @endif

                                    {{ number_format($regularPriceForDisplay) }}
                                    <span class="text-[10px]">تومان</span>
                                </div>
                            @endif

                            <div
                                class="whitespace-nowrap text-sm font-black {{ $hasDiscountForDisplay || $isFreeForDisplay
                                    ? 'text-brand-600 dark:text-brand-400'
                                    : 'text-slate-950 dark:text-slate-100' }}"
                            >
                                @if($isFreeForDisplay)
                                    رایگان!
                                @else
                                    @if($isPriceRange)
                                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">
                                            از
                                        </span>
                                    @endif

                                    {{ number_format($minPrice) }}

                                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">
                                        تومان
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-sm font-black text-slate-400 dark:text-slate-500">
                                نامشخص
                            </span>
                        @endif

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

    </article>
</a>
