@if($product->imageGallery->isNotEmpty())
    <x-front.cart class="relative">
        <x-front.cart-title
            title="گالری تصاویر"
            subtitle="نمونه تصاویر، شات‌ها و خروجی نهایی"
            iconName="image-03"
        />

        <div class="mt-4">
            <div id="product-image-gallery" class="swiper">
                <div class="swiper-wrapper">
                    @foreach($product->imageGallery as $item)
                        <div class="swiper-slide">
                            <div
                                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 cursor-pointer dark:border-slate-700 dark:bg-slate-800"
                                data-lightbox="product-gallery"
                                data-src="{{ $item->mediaFile->url }}"
                                data-title="{{ $item->title }}"
                            >
                                <x-front.responsive-image
                                    :media="$item->mediaFile"
                                    :fallback="$item->mediaFile->url"
                                    :alt="$item->title"
                                    :title="$item->title"
                                    variant="medium_large"
                                    class="w-full aspect-square object-cover transition-transform duration-300 group-hover:scale-[1.05]"
                                    wrapper-class="w-full"
                                    :lazy="true"
                                    :async="true"
                                    sizes="(max-width: 640px) 100vw, 420px"
                                />

                                <div
                                    class="pointer-events-none absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/20 transition duration-300"></div>

                                <div
                                    class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition"></div>

                                @if($item->title)
                                    <div
                                        class="pointer-events-none absolute bottom-3 right-3 left-3 opacity-0 group-hover:opacity-100 transition duration-300">
                                        <div class="text-white text-xs font-bold line-clamp-2">
                                            {{ $item->title }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between absolute gap-2 top-4 left-4">
            <div id="product-image-gallery-pagination-prev"
                 class="h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center cursor-pointer dark:border-slate-700 dark:bg-slate-950 dark:hover:bg-slate-800">
                <span class="text-slate-700 [&_svg]:w-5 [&_svg]:h-5 dark:text-slate-200">
                    @include('icons.arrow-right-01')
                </span>
            </div>

            <div id="product-image-gallery-pagination-next"
                 class="h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center cursor-pointer dark:border-slate-700 dark:bg-slate-950 dark:hover:bg-slate-800">
                <span class="text-slate-700 [&_svg]:w-5 [&_svg]:h-5 dark:text-slate-200">
                    @include('icons.arrow-left-01')
                </span>
            </div>
        </div>
    </x-front.cart>
@endif
