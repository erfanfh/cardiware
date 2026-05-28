@php
    $imageWidth = $product->picture?->primaryVariant?->width;
    $imageHeight = $product->picture?->primaryVariant->height;

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
                            <span
                                class="text-[11px] font-extrabold text-white bg-white/10 px-2 py-1 rounded-xl border border-white/10">FC 26</span>
                            <span
                                class="text-[11px] font-extrabold text-white bg-white/10 px-2 py-1 rounded-xl border border-white/10">PC</span>
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
                            class="h-10 w-10 rounded-2xl bg-white/10 text-white border border-white/10 hover:bg-white/15 transition grid place-items-center"
                            aria-label="علاقه‌مندی">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M12 20s-7-4.35-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 10c0 5.65-7 10-7 10Z"
                                    stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </button>
                        <button
                            class="h-10 w-10 rounded-2xl bg-white/10 text-white border border-white/10 hover:bg-white/15 transition grid place-items-center"
                            aria-label="اشتراک‌گذاری">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M16 8a3 3 0 1 0-2.83-4H13a3 3 0 0 0 .17 1L8.91 7.13a3 3 0 0 0-1.91-.63A3 3 0 1 0 10 9.5c0-.35-.06-.69-.17-1l4.26-2.13A3 3 0 0 0 16 8Zm0 0 0 8m0 0a3 3 0 1 0 2.83 4H19a3 3 0 0 0-.17-1l-4.26-2.13c.11-.31.17-.65.17-1a3 3 0 0 0-.17-1l4.26-2.13c.11.31.17.65.17 1Z"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
