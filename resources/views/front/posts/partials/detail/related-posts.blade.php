@if($relatedPosts->isNotEmpty())
    @php
        $relatedSliderPosts = $relatedPosts->take(6);
        $relatedCompactPosts = $relatedPosts->skip(6)->take(4);
    @endphp

    <x-front.cart id="related-posts" class="overflow-hidden">
        <div class="flex items-start justify-between gap-4">
            <x-front.cart-title
                title="Related posts"
                subtitle="Content you may find useful"
                iconName="arrow-reload-horizontal"
                class="!mb-0"
            />

            @if($relatedSliderPosts->count() > 1)
                <div class="hidden shrink-0 items-center gap-2 sm:flex">
                    <button
                        type="button"
                        class="mwRelatedPostsPrev inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 disabled:pointer-events-none disabled:opacity-35 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-300 [&_svg]:h-4 [&_svg]:w-4"
                        aria-label="Previous posts"
                    >
                        @include('icons.arrow-left-01')
                    </button>

                    <button
                        type="button"
                        class="mwRelatedPostsNext inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 disabled:pointer-events-none disabled:opacity-35 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-300 [&_svg]:h-4 [&_svg]:w-4"
                        aria-label="Next posts"
                    >
                        @include('icons.arrow-right-01')
                    </button>
                </div>
            @endif
        </div>

        <div class="mt-5">
            <div class="swiper mwRelatedPosts !overflow-visible sm:!overflow-hidden">
                <div class="swiper-wrapper">
                    @foreach($relatedSliderPosts as $relatedPost)
                        <div class="swiper-slide !h-auto">
                            <a
                                href="{{ front_route('contents.detail', $relatedPost) }}"
                                class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition duration-300 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-950 dark:hover:border-brand-500/30 dark:hover:shadow-black/20"
                            >
                                <div class="relative overflow-hidden bg-slate-100 dark:bg-slate-800">
                                    <x-front.responsive-image
                                        :media="$relatedPost->picture"
                                        :fallback="$relatedPost->picture?->url"
                                        :alt="$relatedPost->title"
                                        :title="$relatedPost->title"
                                        variant="medium_large"
                                        class="w-full h-auto object-cover transition-transform duration-500 group-hover:scale-[1.035]"
                                        wrapper-class="block w-full"
                                        :lazy="true"
                                        :async="true"
                                        sizes="(max-width: 640px) 82vw, (max-width: 1024px) 45vw, 260px"
                                    />

                                    @if($relatedPost->categories->isNotEmpty())
                                        <div class="absolute right-3 top-3 flex max-w-[calc(100%-1.5rem)] flex-wrap gap-1.5">
                                            @foreach($relatedPost->categories->take(2) as $category)
                                                <span class="rounded-xl bg-black/55 px-2.5 py-1 text-[10px] font-extrabold text-white ring-1 ring-white/15 backdrop-blur-sm">
                                                    {{ $category->title }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="flex flex-1 flex-col p-4">
                                    <div class="flex items-center gap-2 text-[11px] font-bold text-slate-400 dark:text-slate-500">
                                        <span>{{ (($relatedPost->published_at) ? \Illuminate\Support\Carbon::parse($relatedPost->published_at)->format('d F Y') : '') }}</span>
                                        @if($relatedPost->tags->isNotEmpty())
                                            <span class="h-1 w-1 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                            <span class="truncate">{{ $relatedPost->tags->first()->title }}</span>
                                        @endif
                                    </div>

                                    <h3 class="mt-2.5 line-clamp-2 text-sm font-extrabold leading-6 text-slate-900 transition group-hover:text-brand-700 dark:text-slate-100 dark:group-hover:text-brand-300">
                                        {{ $relatedPost->title }}
                                    </h3>

                                    <p class="mt-2 line-clamp-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                        {{ $relatedPost->excerpt() }}
                                    </p>

                                    <div class="mt-auto flex items-center gap-1.5 pt-4 text-xs font-extrabold text-brand-700 dark:text-brand-300 [&_svg]:h-3.5 [&_svg]:w-3.5">
                                        View post
                                        <x-front.arrow-right-02/>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($relatedSliderPosts->count() > 1)
                <div class="mwRelatedPostsPagination mt-4 flex items-center justify-center gap-1.5 [&_.swiper-pagination-bullet-active]:!w-6 [&_.swiper-pagination-bullet-active]:!bg-brand-500 dark:[&_.swiper-pagination-bullet-active]:!bg-brand-400"></div>
            @endif
        </div>

        @if($relatedCompactPosts->isNotEmpty())
            <div class="mt-5 border-t border-slate-100 pt-5 dark:border-slate-800">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-200">
                        More related content
                    </h3>
                    <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500">
                        {{ $relatedCompactPosts->count() }} Other posts
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-x-5 sm:grid-cols-2">
                    @foreach($relatedCompactPosts as $relatedPost)
                        <a
                            href="{{ front_route('contents.detail', $relatedPost) }}"
                            class="group flex min-w-0 items-start gap-3 border-b border-slate-100 py-3 last:border-b-0 sm:[&:nth-last-child(-n+2)]:border-b-0 dark:border-slate-800"
                        >
                            <span class="mt-2.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500 transition group-hover:scale-125"></span>

                            <div class="min-w-0 flex-1">
                                <div class="line-clamp-2 text-sm font-bold leading-6 text-slate-700 transition group-hover:text-brand-700 dark:text-slate-300 dark:group-hover:text-brand-300">
                                    {{ $relatedPost->title }}
                                </div>
                                <div class="mt-1 flex items-center gap-2 text-[11px] text-slate-400 dark:text-slate-500">
                                    <span>{{ (($relatedPost->published_at) ? \Illuminate\Support\Carbon::parse($relatedPost->published_at)->format('d F Y') : '') }}</span>
                                    @if($relatedPost->categories->isNotEmpty())
                                        <span class="h-1 w-1 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                        <span class="truncate">{{ $relatedPost->categories->first()->title }}</span>
                                    @endif
                                </div>
                            </div>

                            <span class="mt-1 shrink-0 text-slate-300 transition group-hover:-translate-x-0.5 group-hover:text-brand-600 dark:text-slate-600 dark:group-hover:text-brand-300 [&_svg]:h-4 [&_svg]:w-4">
                                @include('icons.arrow-right-01')
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </x-front.cart>
@endif
