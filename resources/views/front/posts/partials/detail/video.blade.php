@php
    $videoItems = $post->videoGallery
        ->filter(fn($item) => $item->mediaFile && filled(data_get($item->meta, 'video_url')))
        ->values();

    $videoModalId = 'post-video-gallery-modal-' . $post->getKey();
@endphp

@if($videoItems->isNotEmpty())
    <div
            x-data="postVideoGallery()"
            data-video-modal-id="{{ $videoModalId }}"
            x-init="init($el.dataset.videoModalId)"
    >
        <x-front.cart id="video-gallery" class="overflow-hidden">
            <div class="flex items-start justify-between gap-4">
                <x-front.cart-title
                        title="Video gallery"
                        iconName="video-01"
                        class="!mb-0"
                />

                    <?php if ($videoItems->count() > 1) : ?>
                <div class="hidden shrink-0 items-center gap-2 sm:flex">
                    <button
                            type="button"
                            x-ref="prev"
                            class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 disabled:pointer-events-none disabled:opacity-35 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-300 [&_svg]:h-4 [&_svg]:w-4"
                            aria-label="Previous video"
                    >
                        @include('icons.arrow-left-01')
                    </button>

                    <button
                            type="button"
                            x-ref="next"
                            class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 disabled:pointer-events-none disabled:opacity-35 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-300 [&_svg]:h-4 [&_svg]:w-4"
                            aria-label="Next video"
                    >
                        @include('icons.arrow-right-01')
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <div class="mt-5">
                <div
                        x-ref="slider"
                        class="swiper !overflow-visible sm:!overflow-hidden"
                >
                    <div class="swiper-wrapper">
                            <?php foreach ($videoItems as $item) : ?>
                            <?php
                            $videoUrl = data_get($item->meta, 'video_url');
                            $videoHost = strtolower((string)parse_url($videoUrl, PHP_URL_HOST));
                            $isAparat = in_array($videoHost, ['aparat.com', 'www.aparat.com'], true);
                            ?>

                        <div class="swiper-slide !h-auto">
                            <button
                                    type="button"
                                    data-video-url="{{ $videoUrl }}"
                                    data-video-title="{{ $item->title }}"
                                    data-video-cover="{{ $item->mediaFile->url }}"
                                    @click="openFromElement($el)"
                                    class="group flex h-full w-full cursor-pointer flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white text-start transition duration-300 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-400 focus-visible:ring-offset-2 dark:border-slate-700 dark:bg-slate-950 dark:hover:border-brand-500/30 dark:hover:shadow-black/20 dark:focus-visible:ring-offset-slate-900"
                                    aria-label="Play video {{ $item->title }}"
                            >
                                <div class="relative aspect-video w-full overflow-hidden bg-slate-900">
                                    <div
                                            class="absolute inset-0 h-full w-full transform-gpu transition-transform duration-500 ease-out group-hover:scale-[1.035]"
                                    >
                                        <x-front.responsive-image
                                                :media="$item->mediaFile"
                                                :fallback="$item->mediaFile->url"
                                                :alt="$item->title"
                                                :title="$item->title"
                                                variant="medium_large"
                                                class="block h-full w-full object-cover"
                                                wrapper-class="absolute inset-0 !h-full !w-full"
                                                :lazy="true"
                                                :async="true"
                                                sizes="(max-width: 640px) 88vw, (max-width: 1024px) 48vw, 360px"
                                        />
                                    </div>

                                    <div class="absolute inset-0 bg-slate-950/20 transition duration-300 group-hover:bg-slate-950/35"></div>
                                    <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-slate-950/75 via-slate-950/20 to-transparent"></div>

                                    <div class="absolute right-3 top-3">
                                            <span class="inline-flex items-center gap-1.5 rounded-xl border border-white/15 bg-slate-950/55 px-2.5 py-1 text-[10px] font-extrabold text-white backdrop-blur-md">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $isAparat ? 'bg-red-400' : 'bg-brand-400' }}"></span>
                                                {{ $isAparat ? 'Aparat' : 'Video' }}
                                            </span>
                                    </div>

                                    <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="flex h-14 w-14 items-center justify-center rounded-full border border-white/30 bg-white/90 text-slate-950 shadow-xl backdrop-blur transition duration-300 group-hover:scale-110 group-hover:bg-brand-500 group-hover:text-white sm:h-16 sm:w-16 [&_svg]:h-6 [&_svg]:w-6">
                                                @include('icons.play')
                                            </span>
                                    </div>

                                    <div class="absolute inset-x-4 bottom-4 flex items-end justify-between gap-3">
                                            <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-white">
                                                Play video
                                                <span class="text-white/70 [&_svg]:h-3.5 [&_svg]:w-3.5">
                                                    <x-front.arrow-right-02/>
                                                </span>
                                            </span>
                                    </div>
                                </div>

                                <div class="flex min-h-[72px] w-full items-center px-4 py-3.5 sm:px-5">
                                    <h3 class="line-clamp-2 text-sm font-extrabold leading-6 text-slate-900 transition group-hover:text-brand-700 dark:text-slate-100 dark:group-hover:text-brand-300">
                                        {{ $item->title }}
                                    </h3>
                                </div>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                    <?php if ($videoItems->count() > 1) : ?>
                <div
                        x-ref="pagination"
                        class="mt-4 flex min-h-2 items-center justify-center gap-1.5 [&_.swiper-pagination-bullet]:!m-0 [&_.swiper-pagination-bullet]:!h-2 [&_.swiper-pagination-bullet]:!w-2 [&_.swiper-pagination-bullet]:!rounded-full [&_.swiper-pagination-bullet]:!bg-slate-300 [&_.swiper-pagination-bullet]:!opacity-100 [&_.swiper-pagination-bullet]:!transition-all [&_.swiper-pagination-bullet-active]:!w-6 [&_.swiper-pagination-bullet-active]:!bg-brand-500 dark:[&_.swiper-pagination-bullet]:!bg-slate-700 dark:[&_.swiper-pagination-bullet-active]:!bg-brand-400"
                ></div>
                <?php endif; ?>
            </div>
        </x-front.cart>

        <template x-teleport="body">
            <div
                    :id="modalId"
                    x-show="selectedVideo"
                    style="display: none;"
                    @keydown.escape.window="selectedVideo && closeModal()"
                    class="fixed inset-0 z-[120] flex items-center justify-center p-3 sm:p-6"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Play video"
            >
                <div
                        x-show="selectedVideo"
                        x-transition:enter="transition-opacity duration-200 ease-out"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition-opacity duration-150 ease-in"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"
                        @click="closeModal()"
                        aria-hidden="true"
                ></div>

                <div
                        x-show="selectedVideo"
                        x-transition:enter="transition duration-300 ease-out"
                        x-transition:enter-start="translate-y-4 scale-[0.98] opacity-0"
                        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                        x-transition:leave="transition duration-180 ease-in"
                        x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                        x-transition:leave-end="translate-y-3 scale-[0.98] opacity-0"
                        class="relative z-10 w-full max-w-5xl overflow-hidden rounded-3xl border border-white/10 bg-slate-950 shadow-2xl shadow-black/50"
                        @click.stop
                >
                    <div class="relative aspect-video w-full overflow-hidden bg-black">
                        <template
                                x-if="selectedVideo && selectedVideo.playerType === 'aparat' && selectedVideo.playerUrl">
                            <iframe
                                    :src="selectedVideo.playerUrl"
                                    :title="selectedVideo.title || 'Aparat video'"
                                    class="absolute inset-0 h-full w-full border-0"
                                    allow="autoplay; fullscreen; picture-in-picture"
                                    allowfullscreen
                                    referrerpolicy="strict-origin-when-cross-origin"
                            ></iframe>
                        </template>

                        <template
                                x-if="selectedVideo && selectedVideo.playerType === 'direct' && selectedVideo.playerUrl && !playerError">
                            <video
                                    data-post-video-direct-player
                                    :src="selectedVideo.playerUrl"
                                    :poster="selectedVideo.coverUrl || null"
                                    class="absolute inset-0 h-full w-full bg-black object-contain"
                                    controls
                                    playsinline
                                    preload="metadata"
                                    x-on:error="handleDirectVideoError()"
                            ></video>
                        </template>

                        <template x-if="selectedVideo && (selectedVideo.playerType === 'unsupported' || playerError)">
                            <div class="absolute inset-0 flex flex-col items-center justify-center px-6 text-center">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-white/80 [&_svg]:h-6 [&_svg]:w-6">
                                    @include('icons.video-01')
                                </div>

                                <div class="mt-4 text-sm font-extrabold text-white sm:text-base">
                                    This link cannot be played on the website
                                </div>
                                <div class="mt-2 max-w-md text-xs leading-6 text-slate-400 sm:text-sm">
                                    Enter a direct video file link or an Aparat video page link.
                                </div>

                                <template x-if="selectedVideo.sourceUrl">
                                    <a
                                            :href="selectedVideo.sourceUrl"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="mt-4 inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-xs font-extrabold text-slate-900 transition hover:bg-slate-100"
                                    >
                                        Open video link
                                        <span class="[&_svg]:h-4 [&_svg]:w-4">
                                            <x-front.arrow-right-02/>
                                        </span>
                                    </a>
                                </template>
                            </div>
                        </template>

                        <button
                                type="button"
                                data-post-video-modal-close
                                @click="closeModal()"
                                class="absolute left-3 top-3 z-20 inline-flex h-10 w-10 cursor-pointer items-center justify-center rounded-full border border-white/15 bg-black/55 text-white shadow-lg backdrop-blur-md transition hover:bg-black/80 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/80 sm:left-4 sm:top-4"
                                aria-label="Close video"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.8"
                                 aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="flex items-center justify-between gap-4 border-t border-white/10 bg-slate-950 px-4 py-3.5 sm:px-5 sm:py-4">
                        <div class="min-w-0">
                            <div class="text-[10px] font-extrabold text-brand-400">Video gallery</div>
                            <h3
                                    class="mt-1 truncate text-sm font-extrabold text-white sm:text-base"
                                    x-text="selectedVideo?.title || 'Video'"
                            ></h3>
                        </div>

                        <div class="hidden shrink-0 items-center gap-2 text-[11px] font-bold text-slate-400 sm:flex">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            Play on this page
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endif
