<section id="free-mods">
    <div class="flex items-center gap-2 mb-4">
        @foreach($this->postTabs as $postTab)
            <button wire:click="changeTab($postTab['key'])"
                    class="inline-flex cursor-pointer items-center gap-2 h-10 px-4 rounded-2xl font-extrabold text-sm transition border
                    {{ $postTab['key'] === $activeTabKey ? 'border-brand-100 bg-brand-50 text-slate-900' : 'border-slate-200 bg-white text-slate-800 hover:bg-slate-50' }}">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-xl text-xs font-black
                    {{ $postTab['key'] === $activeTabKey ? 'bg-brand-600 text-white' : 'bg-brand-200 text-brand-900' }}"></span>
                <h2>{{ $postTab['main_title'] }}</h2>
            </button>
        @endforeach
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="p-4">
            @if($this->mainPosts->isNotEmpty())
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 overflow-hidden">
                    <div class="relative p-4">
                        <div class="flex items-stretch justify-center gap-4">
                            @foreach($this->mainPosts as $post)
                                <a href="{{ $post->url }}"
                                   class="group w-[360px] hidden lg:block rounded-2xl border border-slate-200 bg-white overflow-hidden hover:shadow-lg transition">
                                    <div class="p-4">
                                        <div class="relative rounded-2xl overflow-hidden bg-slate-900">
                                            <img src="{{ $post->picture->url }}" alt="{{ $post->title }}"
                                                 title="{{ $post->title }}">

                                            @if($post->features->frontBadge)
                                                <div
                                                    class="absolute top-2 left-2 inline-flex items-center gap-2 rounded-2xl bg-black/55 ring-1 ring-white/15 px-3 py-1 text-xs font-bold text-white">
                                                    {{ $post->features->frontBadge->value }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="mt-4">
                                            <h3 class="text-base font-extrabold text-slate-900 line-clamp-2 leading-7">
                                                {{ $post->title }}
                                            </h3>
                                            <div class="mt-2 text-xs text-slate-500 flex items-center flex-wrap gap-2">
                                                <span>{{ jalali_date($post->published_at, 'd F Y') }}</span>
                                                <span class="h-1 w-1 rounded bg-slate-300"></span>
                                                <span>{{ $post->categories->first()->title }}</span>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex flex-wrap gap-2">
                                            @if($post->features->isSpecial)
                                                <span
                                                    class="inline-flex items-center gap-1.5 rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 [&_svg]:text-yellow-600">
                                                    @include('icons.star')
                                                    ویژه
                                                </span>
                                            @endif

                                            @if($post->features->isVip)
                                                <span
                                                    class="inline-flex items-center gap-1.5 rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 [&_svg]:text-brand-600">
                                                    @include('icons.crown-03')
                                                    VIP
                                                </span>
                                            @endif

                                            @if($post->features->isUpdated)
                                                <span
                                                    class="inline-flex items-center gap-1.5 rounded-xl bg-slate-50 border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 [&_svg]:text-green-600">
                                                    @include('icons.reload')
                                                    آپدیت شد
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mt-4 flex flex-row-reverse items-center justify-between gap-3">
                                            <span
                                                class="inline-flex items-center gap-2 rounded-2xl bg-brand-600 px-4 py-2 text-sm font-extrabold text-white group-hover:bg-brand-700 transition">
                                                دانلود
                                                @include('icons.arrow-left-double')
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <!-- Dots -->
                        <div class="mt-4 flex items-center justify-center gap-3">
                            <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                            <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                            <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                            <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                            <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                            <span class="h-2 w-6 rounded-full bg-brand-600"></span>
                            <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                            <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                            <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                            <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                        </div>

                    </div>
                </div>
            @endif

            @if($this->list1Posts->isNotEmpty() and $this->list2Posts->isNotEmpty())
                <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($this->list1Posts->isNotEmpty())
                        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                            <div class="p-4">
                                <div class="flex items-center justify-end">
                                    <span
                                        class="inline-flex items-center justify-center h-10 px-5 rounded-2xl bg-brand-50 border border-brand-100 text-sm font-extrabold text-brand-700">
                                        {{ $this->list1Title }}
                                    </span>
                                </div>

                                <div class="mt-4 border-t border-slate-100 pt-4 space-y-4">
                                    @foreach($this->list1Posts as $post)
                                        <a href="{{ $post->url }}" class="group flex items-start gap-3">
                                            <span class="mt-2 h-1.5 w-1.5 rounded bg-brand-600"></span>
                                            <h4
                                                class="text-slate-900 font-semibold group-hover:text-brand-700 transition text-sm">
                                                {{ $post->title }}
                                            </h4>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($this->list2Posts->isNotEmpty())
                        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                            <div class="p-4">
                                <div class="flex items-center justify-end">
                                    <span
                                        class="inline-flex items-center justify-center h-10 px-5 rounded-2xl bg-brand-50 border border-brand-100 text-sm font-extrabold text-brand-700">
                                        {{ $this->list2Title }}
                                    </span>
                                </div>

                                <div class="mt-4 border-t border-slate-100 pt-4 space-y-4">
                                    @foreach($this->list2Posts as $post)
                                        <a href="{{ $post->url }}" class="group flex items-start gap-3">
                                            <span class="mt-2 h-1.5 w-1.5 rounded bg-brand-600"></span>
                                            <h4
                                                class="text-slate-900 font-semibold group-hover:text-brand-700 transition text-sm">
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
