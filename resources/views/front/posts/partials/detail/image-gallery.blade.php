@if($post->imageGallery->isNotEmpty())
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
          <span
              class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem]">
              @include('icons.image-03')
          </span>
            <h2 class="text-base font-extrabold">گالری تصاویر</h2>
        </div>

        <div class="mt-4">
            <div class="swiper mwGallery">
                <div class="swiper-wrapper">
                    @foreach($post->imageGallery as $item)
                        <div class="swiper-slide">
                            <img
                                class="h-56 sm:h-64 w-full rounded-2xl border border-slate-200 object-cover cursor-zoom-in"
                                src="{{ $item->mediaFile->url }}"
                                alt="{{ $item->title }}"
                                data-lightbox="post-gallery"
                                data-title="{{ $item->title }}"
                            >
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <div
                        class="mwGalleryPrev h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center cursor-pointer">
                        <span class="text-slate-700 [&_svg]:w-5 [&_svg]:h-5">
                            @include('icons.arrow-right-01')
                        </span>
                    </div>
                    <div class="mwGalleryPagination flex items-center gap-2"></div>
                    <div
                        class="mwGalleryNext h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center cursor-pointer">
                        <span class="text-slate-700 [&_svg]:w-5 [&_svg]:h-5">
                            @include('icons.arrow-left-01')
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
