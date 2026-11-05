@php
    $activePostTab = collect($this->postTabs)->firstWhere('key', $this->activeTabKey);
    $activeTabCategory = $activePostTab['main_category'] ?? null;
@endphp

<section id="free-mods">
    {{-- هویت سبز = رایگان؛ تمایز یک‌نگاهی با محصولات فروشگاه (نارنجی) --}}
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
                <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-lg px-1 text-[10.5px] font-black
                    {{ $postTab['key'] === $activeTabKey ? 'bg-white/25 text-white' : 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' }}">{{ $postTab['posts_count'] ?? 0 }}</span>
                <h2>{{ $postTab['main_title'] }}</h2>
            </button>
        @endforeach

        @if($activePostTab && $activeTabCategory)
            <a href="{{ front_route('posts.categories', $activeTabCategory) }}"
               class="mr-auto inline-flex items-center gap-2 h-10 px-4 rounded-2xl text-sm font-extrabold text-emerald-700 border border-dashed border-emerald-500/40 transition hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-emerald-500/10">
                مشاهده همه
                <span class="[&_svg]:w-4 [&_svg]:h-4">
                    @include('icons.arrow-left-02')
                </span>
            </a>
        @elseif($activePostTab)
            <a href="{{ front_route('posts.list') }}"
               class="mr-auto inline-flex items-center gap-2 h-10 px-4 rounded-2xl text-sm font-extrabold text-emerald-700 border border-dashed border-emerald-500/40 transition hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-emerald-500/10">
                مشاهده همه
                <span class="[&_svg]:w-4 [&_svg]:h-4">
                    @include('icons.arrow-left-02')
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
                {{-- Fancy center loader --}}
                <div class="pointer-events-none absolute inset-0 flex justify-center">
                    <div class="relative top-20">
                        <div
                            class="h-20 w-20 rounded-full bg-white/80 backdrop-blur-md shadow-lg border border-white/70 dark:border-slate-700 dark:bg-slate-900/80 dark:shadow-black/20"></div>

                        <div class="absolute inset-0 flex justify-center top-5">
                            <div class="relative flex justify-center">
                                <span
                                    class="absolute h-10 w-10 rounded-full border-2 border-emerald-200 dark:border-emerald-500/20"></span>

                                <span
                                    class="absolute h-10 w-10 rounded-full border-2 border-transparent border-t-emerald-600 animate-spin"></span>

                                <span
                                    class="h-2.5 w-2.5 rounded-full bg-emerald-600 shadow-[0_0_0_6px_rgba(16,185,129,0.14)] mt-[15px]"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="p-4 transition duration-300"
            wire:loading.class="opacity-40 scale-[0.995]"
            wire:target="changeTab"
        >
            @if($this->mainPosts->isNotEmpty())
                <div
                    class="rounded-2xl border border-emerald-500/10 bg-white/60 overflow-hidden dark:border-emerald-500/10 dark:bg-slate-900/60">
                    <div class="relative p-4">
                        <div class="swiper mwFreeModsMainPosts">
                            <div class="swiper-wrapper">
                                @foreach($this->mainPosts as $post)
                                    <div wire:key="free-mod-main-post-{{ $activeTabKey }}-{{ $post->id }}"
                                         class="swiper-slide !h-auto">
                                        <a href="{{ $post->url }}"
                                           class="group relative z-0 w-full block rounded-2xl border border-transparent bg-white ring-1 ring-slate-200/70 overflow-hidden hover:ring-emerald-400/70 transition duration-300 hover:-translate-y-1 hover:z-10 h-full dark:bg-slate-950 dark:ring-slate-800 dark:hover:ring-emerald-600/50">
                                            <div class="p-4 h-full flex flex-col">
                                                <div
                                                    class="relative aspect-[16/9] rounded-2xl overflow-hidden bg-slate-900">
                                                    <x-front.responsive-image
                                                        :media="$post->picture"
                                                        :fallback="$post->picture?->url"
                                                        :alt="$post->title"
                                                        :title="$post->title"
                                                        variant="medium_large"
                                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]"
                                                        wrapper-class="absolute inset-0 rounded-2xl"
                                                        :lazy="true"
                                                        :async="true"
                                                        :fetchpriority="null"
                                                        sizes="(max-width: 768px) 85vw, 360px"
                                                    />

                                                    {{-- نشان «رایگان» روی تصویر --}}
                                                    <span
                                                        class="absolute top-2 right-2 inline-flex items-center gap-1.5 rounded-xl bg-emerald-500/90 px-2.5 py-1 text-[10.5px] font-black text-white shadow-lg shadow-emerald-950/30 ring-1 ring-white/25 backdrop-blur-sm">
                                                        <span class="[&_svg]:h-3 [&_svg]:w-3">
                                                            @include('icons.download-01')
                                                        </span>
                                                        رایگان
                                                    </span>

                                                    @if($post->features?->frontBadge)
                                                        <div
                                                            class="absolute top-2 left-2 inline-flex items-center gap-2 rounded-2xl bg-black/55 ring-1 ring-white/15 px-3 py-1 text-xs font-bold text-white">
                                                            {{ $post->features->frontBadge->value }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="mt-4">
                                                    <h3 class="text-base font-extrabold text-slate-900 line-clamp-2 leading-7 transition group-hover:text-emerald-700 dark:text-slate-100 dark:group-hover:text-emerald-300">
                                                        {{ $post->title }}
                                                    </h3>
                                                    <div
                                                        class="mt-2 text-xs text-slate-500 flex items-center flex-wrap gap-2 dark:text-slate-400">
                                                        <span>{{ jalali_date($post->published_at, 'd F Y') }}</span>
                                                        <span
                                                            class="h-1 w-1 rounded bg-slate-300 dark:bg-slate-600"></span>
                                                        <span>{{ $post->categories->first()->title }}</span>
                                                    </div>
                                                </div>

                                                <div class="mt-4 flex flex-wrap gap-2">
                                                    @if($post->features?->isSpecial)
                                                        <span
                                                            class="inline-flex items-center gap-1.5 rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 [&_svg]:text-yellow-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                                            @include('icons.star')
                                                            ویژه
                                                        </span>
                                                    @endif

                                                    @if($post->features?->isVip)
                                                        <span
                                                            class="inline-flex items-center gap-1.5 rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 [&_svg]:text-amber-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                                            @include('icons.crown-03')
                                                            VIP
                                                        </span>
                                                    @endif

                                                    @if($post->features?->isUpdated)
                                                        <span
                                                            class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-500/25 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 [&_svg]:text-emerald-600 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                                                            @include('icons.reload')
                                                            آپدیت شد
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="mt-auto pt-4">
                                                    <span
                                                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-l from-emerald-600 to-emerald-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-emerald-500/25 transition duration-300 group-hover:shadow-emerald-500/45 group-hover:brightness-110">
                                                        دانلود رایگان
                                                        @include('icons.download-01')
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>

                            <div
                                class="mt-8 flex items-center justify-center gap-3 mwFreeModsMainPostsPagination"></div>
                        </div>
                    </div>
                </div>
            @endif

            @if($this->list1Posts->isNotEmpty() or $this->list2Posts->isNotEmpty())
                <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach([['posts' => $this->list1Posts, 'title' => $this->list1Title], ['posts' => $this->list2Posts, 'title' => $this->list2Title]] as $listIndex => $list)
                        @if($list['posts']->isNotEmpty())
                            <div
                                class="rounded-2xl border border-emerald-500/10 bg-white/70 overflow-hidden dark:border-emerald-500/10 dark:bg-slate-950/60">
                                <div class="p-4">
                                    <div class="flex items-center justify-between gap-2">
                                        <span
                                            class="inline-flex items-center gap-1.5 text-sm font-extrabold text-slate-800 dark:text-slate-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $list['title'] }}
                                        </span>

                                        @if($activePostTab && $activeTabCategory)
                                            <a href="{{ front_route('posts.categories', $activeTabCategory) }}"
                                               class="text-[11px] font-bold text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400">
                                                همه
                                            </a>
                                        @endif
                                    </div>

                                    <div class="mt-3 border-t border-emerald-500/10 pt-3 space-y-1 dark:border-emerald-500/10">
                                        @foreach($list['posts'] as $post)
                                            <a href="{{ $post->url }}"
                                               class="group -mx-2 flex items-center gap-3 rounded-xl px-2 py-2 transition hover:bg-emerald-50/70 dark:hover:bg-emerald-500/5">
                                                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-emerald-500/10 text-[11px] font-black text-emerald-700 dark:text-emerald-300">
                                                    {{ $loop->iteration }}
                                                </span>

                                                <span class="min-w-0 flex-1">
                                                    <span class="block truncate text-sm font-bold text-slate-800 transition group-hover:text-emerald-700 dark:text-slate-200 dark:group-hover:text-emerald-300">
                                                        {{ $post->title }}
                                                    </span>
                                                </span>

                                                <span class="shrink-0 text-slate-300 transition group-hover:text-emerald-500 group-hover:-translate-x-0.5 dark:text-slate-600 [&_svg]:h-4 [&_svg]:w-4">
                                                    @include('icons.arrow-left-02')
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
