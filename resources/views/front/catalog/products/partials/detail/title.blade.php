@php
    $imageWidth = $product->picture?->primaryVariant?->width;
    $imageHeight = $product->picture?->primaryVariant?->height;

    $aspectRatio = ($imageWidth && $imageHeight)
        ? "{$imageWidth} / {$imageHeight}"
        : "16 / 9";
@endphp

<section
    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20">
    <div class="relative overflow-hidden bg-slate-100 dark:bg-slate-950">
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
            class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/35 via-black/5 to-transparent"></div>

        <div class="absolute left-3 top-3 sm:left-4 sm:top-4">
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

    <div class="relative px-4 py-4 sm:px-5 sm:py-5">
        <div
            class="absolute inset-x-4 top-0 h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent dark:via-slate-700"></div>

        @if($product->categories->isNotEmpty())
            <div class="mb-3 flex flex-wrap items-center gap-2">
                @foreach($product->categories as $category)
                    <span
                        class="inline-flex items-center rounded-xl border border-brand-100 bg-brand-50 px-2.5 py-1 text-[0.6875rem] font-extrabold text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                        {{ $category->title }}
                    </span>
                @endforeach
            </div>
        @endif

        <div class="w-full">
            <h1 class="text-xl font-black leading-8 tracking-tight text-slate-900 sm:text-2xl sm:leading-9 dark:text-white">
                {{ $product->title }}
            </h1>

            <p class="mt-2 line-clamp-2 text-sm text-justify leading-7 text-slate-600 dark:text-slate-400">
                {{ $product->excerpt() }}
            </p>
        </div>
    </div>
</section>
