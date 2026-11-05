<x-front.cart class="!p-4 sm:!p-5 border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/90">
    <div class="flex items-center gap-3">
        <div
            class="h-12 w-12 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
            <x-front.responsive-image
                :media="$product->store->picture"
                :fallback="$product->store->picture?->url"
                :alt="$product->store->title"
                :title="$product->store->title"
                variant="thumbnail"
                class="h-full w-full object-cover"
                wrapper-class="h-full w-full rounded-2xl"
                :skeleton="true"
                loading="lazy"
                decoding="async"
                sizes="48px"
            />
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="line-clamp-1 text-sm font-extrabold text-slate-900 dark:text-white">
                    {{ $product->store->title }}
                </h3>

                <span
                    class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">
                    تایید شده
                </span>
            </div>

            @if($product->store->activity_field)
                <p class="mt-1 line-clamp-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                    {{ $product->store->activity_field }}
                </p>
            @endif
        </div>
    </div>

    <div @class(['mt-4 grid gap-2', 'grid-cols-1' => $product->store->hide_sales_count, 'grid-cols-2' => !$product->store->hide_sales_count])>
        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/70">
            <div class="text-[11px] text-slate-500 dark:text-slate-400">تعداد محصولات</div>
            <div class="mt-1 text-sm font-extrabold text-slate-900 dark:text-white">
                {{ number_format($storeProducts) }}
            </div>
        </div>

        @unless($product->store->hide_sales_count)
            <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/70">
                <div class="text-[11px] text-slate-500 dark:text-slate-400">تعداد فروش</div>
                <div class="mt-1 text-sm font-extrabold text-slate-900 dark:text-white">
                    {{ number_format($storeSoldQuantity) }}
                </div>
            </div>
        @endunless
    </div>

    <p class="mt-4 text-justify text-xs leading-6 text-slate-600 dark:text-slate-400">
        {{ $product->store->excerpt(35) }}
    </p>

    <a
        href="{{ filled($product->store?->slug) ? route('stores.detail', $product->store) : '#' }}"
        class="mt-4 flex h-10 w-full items-center justify-center rounded-xl bg-brand-500 px-4 text-xs font-bold text-white transition hover:bg-brand-600"
    >
        مشاهده فروشگاه
    </a>
</x-front.cart>
