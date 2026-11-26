@php
    $activePostTab = collect($this->postTabs)->firstWhere('key', $this->activeTabKey);
    $activeTabCategory = $activePostTab['main_category'] ?? null;

    // Component: pinned items + New = mainPosts (Cards) / list1Posts (List)
    $heroPosts = $this->mainPosts;
    $listPosts = $this->list1Posts;
@endphp

<section id="free-mods">
    {{-- Green identity = Free --}}
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <span class="mr-1 inline-flex h-9 w-9 items-center justify-center rounded-2xl border border-emerald-500/25 bg-emerald-500/10 text-emerald-600 dark:text-emerald-300 [&_svg]:h-[1.1rem] [&_svg]:w-[1.1rem]">
            @include('icons.cloud-download')
        </span>

        @foreach($this->postTabs as $postTab)
            <button
                wire:key="post-tab-{{ $postTab['key'] }}"
                wire:click="changeTab('{{ $postTab['key'] }}')"
                wire:loading.attr="disabled"
                wire:target="changeTab"
                class="inline-flex cursor-pointer items-center gap-2 h-10 px-4 rounded-2xl font-extrabold text-sm transition border disabled:opacity-70
                {{ $postTab['key'] === $activeTabKey
                    ? 'border-emerald-500/60 bg-gradient-to-l from-emerald-600 to-emerald-500 text-white shadow-lg shadow-emerald-600/25'
                    : 'border-white/60 bg-white/70 text-slate-700 backdrop-blur-xl hover:bg-white dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10' }}"
            >
                {{-- Counter: today’s posts, not total) --}}
                <span
                    title="{{ ($postTab['today_count'] ?? 0) > 0 ? 'Today ' . $postTab['today_count'] . ' new posts published in this category' : 'No new posts published in this category today' }}"
                    class="inline-flex h-5 min-w-5 items-center justify-center rounded-lg px-1 text-[10.5px] font-black
                    {{ $postTab['key'] === $activeTabKey ? 'bg-white/25 text-white' : 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' }}">
                    @if(($postTab['today_count'] ?? 0) > 0)
                        {{ $postTab['today_count'] }}
                    @else
                        <span class="[&_svg]:h-3 [&_svg]:w-3 opacity-60">@include('icons.calendar-03')</span>
                    @endif
                </span>
                <h2>{{ $postTab['main_title'] }}</h2>
            </button>
        @endforeach

        @if($activePostTab && $activeTabCategory)
            <a href="{{ front_route('posts.categories', $activeTabCategory) }}"
               class="mr-auto inline-flex items-center gap-2 h-10 px-4 rounded-2xl text-sm font-extrabold text-emerald-700 border border-dashed border-emerald-500/40 transition hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-emerald-500/10">
                View all
                <span class="[&_svg]:w-4 [&_svg]:h-4">
                    <x-front.arrow-right-02/>
                </span>
            </a>
        @elseif($activePostTab)
            <a href="{{ front_route('posts.list') }}"
               class="mr-auto inline-flex items-center gap-2 h-10 px-4 rounded-2xl text-sm font-extrabold text-emerald-700 border border-dashed border-emerald-500/40 transition hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-emerald-500/10">
                View all
                <span class="[&_svg]:w-4 [&_svg]:h-4">
                    <x-front.arrow-right-02/>
                </span>
            </a>
        @endif
    </div>

    <div
        class="relative rounded-3xl border border-emerald-500/15 bg-gradient-to-b from-emerald-50/60 to-white shadow-sm overflow-hidden dark:border-emerald-500/10 dark:from-emerald-950/25 dark:to-slate-950 dark:shadow-black/20">
        {{-- Loading Overlay --}}
        <div
            wire:loading.flex
            wire:target="changeTab"
            class="absolute inset-0 z-20 items-center justify-center bg-white/70 backdrop-blur-sm dark:bg-slate-950/70"
        >
            <div class="relative w-full p-4">
                <div class="pointer-events-none absolute inset-0 flex justify-center">
                    <div class="relative top-20">
                        <div class="h-20 w-20 rounded-full bg-white/80 backdrop-blur-md shadow-lg border border-white/70 dark:border-slate-700 dark:bg-slate-900/80 dark:shadow-black/20"></div>
                        <div class="absolute inset-0 flex justify-center top-5">
                            <div class="relative flex justify-center">
                                <span class="absolute h-10 w-10 rounded-full border-2 border-emerald-200 dark:border-emerald-500/20"></span>
                                <span class="absolute h-10 w-10 rounded-full border-2 border-transparent border-t-emerald-600 animate-spin"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-600 shadow-[0_0_0_6px_rgba(16,185,129,0.14)] mt-[15px]"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="p-4 sm:p-5 transition duration-300"
            wire:loading.class="opacity-40 scale-[0.995]"
            wire:target="changeTab"
        >
            {{-- ═══════ Large cards (3 base + Pinned = Slider if >3) ═══════ --}}
            @if($heroPosts->isNotEmpty())
                @php($heroActual = $heroPosts->count())
                <div wire:key="free-hero-{{ $activeTabKey }}-{{ $heroActual }}" class="{{ $heroActual > 3 ? 'swiper mwFreeHeroCards' : 'grid grid-cols-1 md:grid-cols-3 gap-4' }}">
                    @if($heroActual > 3)<div class="swiper-wrapper">@endif
                        @foreach($heroPosts as $post)
                            <div wire:key="hero-{{ $activeTabKey }}-{{ $post->id }}"
                                 @if($heroActual > 3) class="swiper-slide !h-auto" @endif>
                                <a href="{{ $post->url }}"
                                   class="group relative z-0 block h-full overflow-hidden rounded-2xl border border-transparent bg-white ring-1 ring-slate-200/70 transition duration-300 hover:ring-emerald-400/70 hover:shadow-lg hover:shadow-emerald-500/10 hover:-translate-y-0.5 dark:bg-slate-950 dark:ring-slate-800 dark:hover:ring-emerald-600/50">
                                    <div class="relative aspect-[16/9] overflow-hidden bg-slate-100 dark:bg-slate-800">
                                        <x-front.responsive-image
                                            :media="$post->picture"
                                            :fallback="$post->picture?->url"
                                            :alt="$post->title"
                                            :title="$post->title"
                                            variant="medium_large"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                            wrapper-class="absolute inset-0"
                                            :lazy="true"
                                            :async="true"
                                            :fetchpriority="null"
                                            sizes="(max-width: 768px) 90vw, 360px"
                                        />
                                        <span class="absolute top-2.5 right-2.5 grid h-7 w-7 place-items-center rounded-xl bg-emerald-500/90 text-white shadow-md ring-1 ring-white/25 backdrop-blur-sm transition group-hover:scale-110 [&_svg]:h-3.5 [&_svg]:w-3.5" title="Free download">
                                            @include('icons.download-01')
                                        </span>
                                        @if($post->is_pinned)
                                            <span class="absolute top-2.5 left-2.5 grid h-7 w-7 place-items-center rounded-xl bg-amber-500/90 text-white shadow-md ring-1 ring-white/25 backdrop-blur-sm" title="Pinned post">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 17v5"/><path d="M9 10.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24V16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V7a1 1 0 0 1 1-1 2 2 0 0 0 0-4H8a2 2 0 0 0 0 4 1 1 0 0 1 1 1z"/>
                                                </svg>
                                            </span>
                                        @elseif($post->features?->frontBadge)
                                            <span class="absolute top-2.5 left-2.5 rounded-lg bg-black/55 ring-1 ring-white/15 px-2 py-0.5 text-[10px] font-bold text-white backdrop-blur-sm">
                                                {{ $post->features->frontBadge->value }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="p-3.5">
                                        <h3 class="text-sm font-extrabold leading-6 text-slate-900 line-clamp-2 transition group-hover:text-emerald-700 dark:text-slate-100 dark:group-hover:text-emerald-300">
                                            {{ $post->title }}
                                        </h3>
                                        <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500">
                                            <span>{{ time_ago($post->published_at) }}</span>
                                            <span class="inline-flex items-center gap-1 font-bold text-emerald-600 transition group-hover:gap-1.5 dark:text-emerald-400">
                                                View
                                                <span class="[&_svg]:h-3.5 [&_svg]:w-3.5">
                                                    <x-front.arrow-right-02/>
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    @if($heroActual > 3)</div>
                    <div class="mt-3 flex items-center justify-center gap-2 mwFreeHeroPagination"></div>@endif
                </div>
            @endif

            {{-- ═══════ Compact list ═══════ --}}
            @if($listPosts->isNotEmpty())
                <div class="mt-4 rounded-2xl border border-emerald-500/10 bg-white/70 dark:border-emerald-500/10 dark:bg-slate-900/50">
                    <div class="p-3">
                        <div class="grid grid-cols-1 gap-0.5 sm:grid-cols-2">
                            @foreach($listPosts as $post)
                                <a href="{{ $post->url }}"
                                   wire:key="list-{{ $activeTabKey }}-{{ $post->id }}"
                                   class="group -mx-1 flex items-center gap-3 rounded-xl px-2.5 py-2 transition hover:bg-emerald-50/70 dark:hover:bg-emerald-500/5">
                                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-slate-100 text-[10px] font-black text-slate-500 transition group-hover:bg-emerald-500/15 group-hover:text-emerald-700 dark:bg-slate-800 dark:text-slate-400 dark:group-hover:text-emerald-300">
                                        {{ $loop->iteration }}
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-[13px] font-bold leading-5 text-slate-700 transition group-hover:text-emerald-700 dark:text-slate-300 dark:group-hover:text-emerald-300">
                                            {{ $post->title }}
                                        </span>
                                    </span>
                                    <span class="shrink-0 text-[10px] font-bold text-slate-400 dark:text-slate-500">
                                        {{ time_ago($post->published_at) }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
