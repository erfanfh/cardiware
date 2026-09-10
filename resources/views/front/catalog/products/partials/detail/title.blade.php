@php
    $imageWidth = $product->picture?->primaryVariant?->width;
    $imageHeight = $product->picture?->primaryVariant?->height;

    $aspectRatio = ($imageWidth && $imageHeight)
        ? "{$imageWidth} / {$imageHeight}"
        : "16 / 9";
@endphp

<section
    class="relative isolate overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20"
>
    <div
        class="pointer-events-none absolute -right-24 -top-24 z-0 h-56 w-56 rounded-full bg-brand-100/60 blur-3xl dark:bg-brand-500/10"
    ></div>

    <div class="relative z-10 px-4 py-4">
        <h1
            class="text-base font-black leading-8 tracking-tight text-slate-950 sm:text-xl sm:leading-9 lg:text-[1.15rem] lg:leading-10 dark:text-white"
        >
            {{ $product->title }}
        </h1>
    </div>

    <div class="relative z-10 overflow-hidden bg-slate-100 dark:bg-slate-950">
        <div
            style="aspect-ratio: {{ $aspectRatio }};"
            class="w-full min-h-[260px] overflow-hidden sm:min-h-[320px] lg:min-h-0"
        >
            <x-front.responsive-image
                :media="$product->picture"
                :fallback="$product->picture?->url"
                :alt="$product->title"
                :title="$product->title"
                variant="large"
                class="h-full w-full object-cover transition-transform duration-700 hover:scale-[1.02]"
                wrapper-class="h-full w-full"
                :lazy="false"
                :async="false"
                :skeleton="true"
                fetchpriority="high"
                sizes="(max-width: 640px) 100vw, (max-width: 1024px) 100vw, 900px"
            />
        </div>

        <div
            class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/35 via-black/5 to-transparent"
        ></div>

        <div class="absolute left-3 top-3 flex items-center gap-2 sm:left-4 sm:top-4" dir="ltr">
            <livewire:front.catalog.product-favorite-button
                :product="$product"
                :key="'product-favorite-button-' . $product->id"
            />

            <button
                x-data
                x-on:click="$dispatch('open-modal', 'product-share-{{ $product->id }}')"
                class="grid h-10 w-10 cursor-pointer place-items-center rounded-2xl border border-white/15 bg-black/25 text-white shadow-lg shadow-black/10 backdrop-blur-md transition hover:bg-black/35 [&_svg]:h-5 [&_svg]:w-5"
                aria-label="اشتراک‌گذاری"
            >
                @include('icons.share-08')
            </button>
        </div>
    </div>

    <div class="relative z-10 px-4 py-4 sm:px-5 sm:py-5">
        <div
            class="absolute inset-x-4 top-0 h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent dark:via-slate-700"
        ></div>

        <p class="line-clamp-3 text-sm text-justify leading-7 text-slate-600 sm:line-clamp-2 dark:text-slate-400">
            {{ $product->excerpt() }}
        </p>

        @if($product->categories->isNotEmpty() || $product->tags->isNotEmpty())
            <div
                class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-200/70 pt-3 dark:border-slate-800">
                @foreach($product->categories as $category)
                    <a
                        href="{{ front_route('products.categories', ['path' => $category->path]) }}"
                        class="inline-flex items-center rounded-xl border border-brand-200/80 bg-brand-50 px-2.5 py-1 text-[0.6875rem] font-extrabold text-brand-700 transition hover:border-brand-300 hover:bg-brand-100 hover:text-brand-800 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 dark:hover:border-brand-500/40 dark:hover:bg-brand-500/15 dark:hover:text-brand-200"
                    >
                        {{ $category->title }}
                    </a>
                @endforeach

                @foreach($product->tags as $tag)
                    <a
                        href="{{ front_route('products.tags', ['path' => $tag->path]) }}"
                        class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1 text-[0.6875rem] font-extrabold text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-300"
                    >
                        #{{ $tag->title }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
