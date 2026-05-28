@php
    $imageWidth = $product->picture?->primaryVariant?->width;
    $imageHeight = $product->picture?->primaryVariant?->height;

    $aspectRatio = ($imageWidth && $imageHeight)
        ? "{$imageWidth} / {$imageHeight}"
        : "16 / 9";
@endphp

<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="relative">
        <div style="aspect-ratio: {{ $aspectRatio }};" class="w-full overflow-hidden">
            <x-front.responsive-image
                :media="$product->picture"
                :fallback="$product->picture?->url"
                :alt="$product->title"
                :title="$product->title"
                variant="large"
                class="w-full h-full object-cover"
                wrapper-class="w-full h-full"
                :lazy="false"
                :async="false"
                :skeleton="true"
                fetchpriority="high"
                sizes="(max-width: 768px) 100vw, 1280px"
            />
        </div>

        <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-black/10 to-transparent"></div>

        <div class="absolute bottom-4 right-4 left-4">
            <div class="rounded-3xl bg-white/10 ring-1 ring-white/10 backdrop-blur p-3 sm:p-4">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            @foreach($product->categories as $category)
                                <span
                                    class="text-[0.6875rem] font-extrabold text-white bg-white/10 px-2 py-1 rounded-xl border border-white/10">
                                    {{ $category->title }}
                                </span>
                            @endforeach
                        </div>

                        <h1 class="text-xl sm:text-2xl font-black text-white leading-tight">
                            {{ $product->title }}
                        </h1>

                        <p class="mt-2 text-white/80 text-sm leading-7 max-w-2xl line-clamp-1">
                            {{ $product->excerpt() }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2 self-start sm:self-end">
                        <button
                            class="cursor-pointer h-10 w-10 [&_svg]:w-5 [&_svg]:h-5 rounded-2xl bg-white/10 text-white border border-white/10 hover:bg-white/15 transition grid place-items-center"
                            aria-label="علاقه‌مندی">
                            @include('icons.favourite')
                        </button>

                        <button
                            x-data
                            x-on:click="$dispatch('open-modal', 'product-share-{{ $product->id }}')"
                            class="cursor-pointer h-10 w-10 [&_svg]:w-5 [&_svg]:h-5 rounded-2xl bg-white/10 text-white border border-white/10 hover:bg-white/15 transition grid place-items-center"
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
