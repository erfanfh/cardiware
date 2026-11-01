@php
    $activePostTab = collect($this->postTabs)->firstWhere('key', $this->activeTabKey);
    $activeTabCategory = $activePostTab['main_category'] ?? null;
@endphp

<section id="free-mods">
    <div class="flex flex-wrap items-center gap-2 mb-4">
        @foreach($this->postTabs as $postTab)
            <button
                wire:key="post-tab-{{ $postTab['key'] }}"
                wire:click="changeTab('{{ $postTab['key'] }}')"
                wire:loading.attr="disabled"
                wire:target="changeTab"
                class="inline-flex cursor-pointer items-center gap-2 h-10 px-4 rounded-2xl font-extrabold text-sm transition border disabled:opacity-70
                {{ $postTab['key'] === $activeTabKey ? 'border-brand-100 bg-brand-50 text-slate-900 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-slate-100' : 'border-slate-200 bg-white text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800' }}"
            >
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-lg text-[10.5px] font-black
                    {{ $postTab['key'] === $activeTabKey ? 'bg-brand-600 text-white' : 'bg-brand-200 text-brand-900 dark:bg-brand-500/20 dark:text-brand-200' }}">{{ $postTab['posts_count'] ?? 0 }}</span>
                <h2>{{ $postTab['main_title'] }}</h2>
            </button>
        @endforeach

        @if($activePostTab && $activeTabCategory)
            <a href="{{ front_route('posts.categories', $activeTabCategory) }}"
               class="mr-auto inline-flex items-center gap-2 h-10 px-4 rounded-2xl text-sm font-extrabold text-brand-700 border border-dashed border-brand-200 transition hover:bg-brand-50 dark:text-brand-300 dark:border-brand-500/30 dark:hover:bg-brand-500/10">
                مشاهده همه {{ $activePostTab['main_title'] }}
                <span class="[&_svg]:w-4 [&_svg]:h-4">
                    @include('icons.arrow-left-02')
                </span>
            </a>
        @elseif($activePostTab)
            <a href="{{ front_route('posts.list') }}"
               class="mr-auto inline-flex items-center gap-2 h-10 px-4 rounded-2xl text-sm font-extrabold text-brand-700 border border-dashed border-brand-200 transition hover:bg-brand-50 dark:text-brand-300 dark:border-brand-500/30 dark:hover:bg-brand-500/10">
                مشاهده همه {{ $activePostTab['main_title'] }}
                <span class="[&_svg]:w-4 [&_svg]:h-4">
                    @include('icons.arrow-left-02')
                </span>
            </a>
        @endif
    </div>

    <div
        class="relative rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
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
                                    class="absolute h-10 w-10 rounded-full border-2 border-brand-200 dark:border-brand-500/20"></span>
                                <span
                                    class="absolute h-10 w-10 rounded-full border-2 border-transparent border-t-brand-600 animate-spin"></span>
                                <span
                                    class="h-2.5 w-2.5 rounded-full bg-brand-600 shadow-[0_0_0_6px_rgba(249,115,22,0.14)] mt-[15px]"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- skeleton main cards --}}
                <div
                    class="rounded-2xl border border-slate-200 bg-slate-50/70 overflow-hidden dark:border-slate-700 dark:bg-slate-900/70">
                    <div class="p-4">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            @for($i = 0; $i < 3; $i++)
                                <div
                                    class="rounded-2xl border border-slate-200 bg-white overflow-hidden dark:border-slate-700 dark:bg-slate-950">
                                    <div class="p-4 animate-pulse">
                                        <div class="rounded-2xl bg-slate-200 h-52 w-full dark:bg-slate-800"></div>

                                        <div class="mt-4 space-y-3">
                                            <div class="h-4 w-3/4 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
                                            <div class="h-4 w-2/3 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
                                        </div>

                                        <div class="mt-4 flex gap-2">
                                            <div class="h-8 w-20 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
                                            <div class="h-8 w-16 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
                                            <div class="h-8 w-24 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
                                        </div>

                                        <div class="mt-4 h-10 w-28 rounded-2xl bg-slate-200 dark:bg-slate-800"></div>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <div class="mt-4 flex items-center justify-center gap-3">
                            <span class="h-2 w-2 rounded-full bg-slate-300 animate-pulse dark:bg-slate-700"></span>
                            <span class="h-2 w-2 rounded-full bg-slate-300 animate-pulse dark:bg-slate-700"></span>
                            <span class="h-2 w-6 rounded-full bg-brand-300 animate-pulse dark:bg-brand-500/40"></span>
                            <span class="h-2 w-2 rounded-full bg-slate-300 animate-pulse dark:bg-slate-700"></span>
                            <span class="h-2 w-2 rounded-full bg-slate-300 animate-pulse dark:bg-slate-700"></span>
                        </div>
                    </div>
                </div>

                {{-- skeleton list boxes --}}
                <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @for($i = 0; $i < 2; $i++)
                        <div
                            class="rounded-2xl border border-slate-200 bg-white overflow-hidden dark:border-slate-700 dark:bg-slate-950">
                            <div class="p-4 animate-pulse">
                                <div class="flex items-center justify-end">
                                    <div class="h-10 w-28 rounded-2xl bg-slate-200 dark:bg-slate-800"></div>
                                </div>

                                <div class="mt-4 border-t border-slate-100 pt-4 space-y-4 dark:border-slate-800">
                                    @for($j = 0; $j < 4; $j++)
                                        <div class="flex items-start gap-3">
                                            <span
                                                class="mt-2 h-1.5 w-1.5 rounded bg-slate-200 dark:bg-slate-800"></span>
                                            <div class="h-4 w-full rounded-xl bg-slate-200 dark:bg-slate-800"></div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    @endfor
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
                    class="rounded-2xl border border-slate-200 bg-slate-50/70 overflow-hidden dark:border-slate-700 dark:bg-slate-900/70">
                    <div class="relative p-4">
                        <div class="swiper mwFreeModsMainPosts">
                            <div class="swiper-wrapper">
                                @foreach($this->mainPosts as $post)
                                    <div wire:key="free-mod-main-post-{{ $activeTabKey }}-{{ $post->id }}"
                                         class="swiper-slide !h-auto">
                                        <a href="{{ $post->url }}"
                                           class="group w-full block rounded-2xl border border-slate-200 bg-white overflow-hidden hover:scale-[97%] transition duration-300 h-full dark:border-slate-700 dark:bg-slate-950">
                                            <div class="p-4 h-full flex flex-col">
                                                <div
                                                    class="relative aspect-[16/9] rounded-2xl overflow-hidden bg-slate-900">
                                                    <x-front.responsive-image
                                                        :media="$post->picture"
                                                        :fallback="$post->picture?->url"
                                                        :alt="$post->title"
                                                        :title="$post->title"
                                                        variant="medium_large"
                                                        class="h-full w-full object-cover"
                                                        wrapper-class="absolute inset-0 rounded-2xl"
                                                        :lazy="true"
                                                        :async="true"
                                                        :fetchpriority="null"
                                                        sizes="(max-width: 768px) 85vw, 360px"
                                                    />

                                                    @if($post->features?->frontBadge)
                                                        <div
                                                            class="absolute top-2 left-2 inline-flex items-center gap-2 rounded-2xl bg-black/55 ring-1 ring-white/15 px-3 py-1 text-xs font-bold text-white">
                                                            {{ $post->features->frontBadge->value }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="mt-4">
                                                    <h3 class="text-base font-extrabold text-slate-900 line-clamp-2 leading-7 dark:text-slate-100">
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
                                                            class="inline-flex items-center gap-1.5 rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 [&_svg]:text-brand-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                                            @include('icons.crown-03')
                                                            VIP
                                                        </span>
                                                    @endif

                                                    @if($post->features?->isUpdated)
                                                        <span
                                                            class="inline-flex items-center gap-1.5 rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 [&_svg]:text-green-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                                            @include('icons.reload')
                                                            آپدیت شد
                                                        </span>
                                                    @endif
                                                </div>

                                                <div
                                                    class="mt-4 flex flex-row-reverse items-center justify-between gap-3 mt-auto">
                                                    <span
                                                        class="inline-flex items-center gap-2 rounded-2xl bg-brand-600 px-4 py-2 text-sm font-extrabold text-white group-hover:bg-brand-700 transition dark:group-hover:bg-brand-500">
                                                        دانلود
                                                        @include('icons.arrow-left-double')
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

            @if($this->list1Posts->isNotEmpty() and $this->list2Posts->isNotEmpty())
                <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($this->list1Posts->isNotEmpty())
                        <div
                            class="rounded-2xl border border-slate-200 bg-white overflow-hidden dark:border-slate-700 dark:bg-slate-950">
                            <div class="p-4">
                                <div class="flex items-center justify-end">
                                    <span
                                        class="inline-flex items-center justify-center h-10 px-5 rounded-2xl bg-brand-50 border border-brand-100 text-sm font-extrabold text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                        {{ $this->list1Title }}
                                    </span>
                                </div>

                                <div class="mt-4 border-t border-slate-100 pt-4 space-y-4 dark:border-slate-800">
                                    @foreach($this->list1Posts as $post)
                                        <a href="{{ $post->url }}" class="group flex items-start gap-3">
                                            <span class="mt-2 h-1.5 w-1.5 rounded bg-brand-600"></span>
                                            <h4
                                                class="text-slate-900 font-semibold group-hover:text-brand-700 transition text-sm dark:text-slate-200 dark:group-hover:text-brand-300">
                                                {{ $post->title }}
                                            </h4>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($this->list2Posts->isNotEmpty())
                        <div
                            class="rounded-2xl border border-slate-200 bg-white overflow-hidden dark:border-slate-700 dark:bg-slate-950">
                            <div class="p-4">
                                <div class="flex items-center justify-end">
                                    <span
                                        class="inline-flex items-center justify-center h-10 px-5 rounded-2xl bg-brand-50 border border-brand-100 text-sm font-extrabold text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                        {{ $this->list2Title }}
                                    </span>
                                </div>

                                <div class="mt-4 border-t border-slate-100 pt-4 space-y-4 dark:border-slate-800">
                                    @foreach($this->list2Posts as $post)
                                        <a href="{{ $post->url }}" class="group flex items-start gap-3">
                                            <span class="mt-2 h-1.5 w-1.5 rounded bg-brand-600"></span>
                                            <h4
                                                class="text-slate-900 font-semibold group-hover:text-brand-700 transition text-sm dark:text-slate-200 dark:group-hover:text-brand-300">
                                                {{ $post->title }}
                                            </h4>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>
