@if($post->imageGallery->isNotEmpty())
    <section
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
        <div class="flex items-center gap-3">
          <span
              class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem] dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
              @include('icons.image-03')
          </span>
            <h2 class="text-base font-extrabold dark:text-slate-100">گالری تصاویر</h2>
        </div>

        <div class="mt-4">
            <div class="swiper mwGallery">
                <div class="swiper-wrapper">
                    @foreach($post->imageGallery as $item)
                        <div class="swiper-slide">
                            <div
                                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 cursor-pointer dark:border-slate-700 dark:bg-slate-800"
                                data-lightbox="post-gallery"
                                data-src="{{ $item->mediaFile->url }}"
                                data-title="{{ $item->title }}"
                            >
                                <img
                                    class="h-56 sm:h-64 w-full object-cover transition duration-500 group-hover:scale-[1.05]"
                                    src="{{ $item->mediaFile->url }}"
                                    alt="{{ $item->title }}"
                                    loading="lazy"
                                >

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

                <div class="mt-4 flex items-center justify-between">
                    <div
                        class="mwGalleryPrev h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center cursor-pointer dark:border-slate-700 dark:bg-slate-950 dark:hover:bg-slate-800">
                        <span class="text-slate-700 [&_svg]:w-5 [&_svg]:h-5 dark:text-slate-200">
                            @include('icons.arrow-right-01')
                        </span>
                    </div>
                    <div class="mwGalleryPagination flex items-center gap-2"></div>
                    <div
                        class="mwGalleryNext h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center cursor-pointer dark:border-slate-700 dark:bg-slate-950 dark:hover:bg-slate-800">
                        <span class="text-slate-700 [&_svg]:w-5 [&_svg]:h-5 dark:text-slate-200">
                            @include('icons.arrow-left-01')
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
