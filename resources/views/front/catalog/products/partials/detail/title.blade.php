@php
    $imageWidth = $product->picture?->primaryVariant?->width;
    $imageHeight = $product->picture?->primaryVariant?->height;

    $aspectRatio = ($imageWidth && $imageHeight)
        ? "{$imageWidth} / {$imageHeight}"
        : "16 / 9";
@endphp

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="relative">
        <div style="aspect-ratio: {{ $aspectRatio }};" class="w-full min-h-[320px] overflow-hidden sm:min-h-0">
            <x-front.responsive-image
                :media="$product->picture"
                :fallback="$product->picture?->url"
                :alt="$product->title"
                :title="$product->title"
                variant="large"
                class="h-full w-full object-cover"
                wrapper-class="h-full w-full"
                :lazy="false"
                :async="false"
                :skeleton="true"
                fetchpriority="high"
                sizes="(max-width: 640px) 100vw, (max-width: 1024px) 100vw, 900px"
            />
        </div>

        <div
            class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/25 to-transparent sm:from-black/50 sm:via-black/10"></div>

        <div class="absolute inset-x-3 bottom-3 sm:inset-x-4 sm:bottom-4">
            <div class="rounded-2xl bg-white/10 p-3 ring-1 ring-white/10 backdrop-blur sm:rounded-3xl sm:p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="min-w-0">
                        <div class="mb-3 flex flex-wrap items-center gap-2 sm:mb-4">
                            @foreach($product->categories as $category)
                                <span
                                    class="rounded-xl border border-white/10 bg-white/10 px-2 py-1 text-[10px] font-extrabold text-white sm:text-[0.6875rem]">
                                    {{ $category->title }}
                                </span>
                            @endforeach
                        </div>

                        <h1 class="text-lg font-black leading-8 text-white sm:text-2xl sm:leading-9">
                            {{ $product->title }}
                        </h1>

                        <p class="mt-2 line-clamp-2 max-w-2xl text-xs leading-6 text-white/80 sm:line-clamp-1 sm:text-sm sm:leading-7">
                            {{ $product->excerpt() }}
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-2 self-start sm:self-end">
                        <button
                            x-data
                            x-on:click="$dispatch('open-modal', 'product-share-{{ $product->id }}')"
                            class="grid h-10 w-10 cursor-pointer place-items-center rounded-2xl border border-white/10 bg-white/10 text-white transition hover:bg-white/15 [&_svg]:h-5 [&_svg]:w-5"
                            aria-label="اشتراک‌گذاری"
                        >
                            @include('icons.share-08')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
