<section id="video-tutorials">
    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-2">
            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 border border-brand-100 text-brand-700">
                @include('icons.play')
            </span>
            <h3 class="text-base font-extrabold">{{ get_setting('home', 'videos_title') }}</h3>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $posts = \App\Domain\Content\Models\Post::whereHas('categories', function ($query) {
                        $query->whereIn('id', get_setting('home', 'videos_category_ids'));
                    })->limit(4)->get();
                @endphp

                @foreach($posts as $post)
                    <a href="{{ route('contents.detail', $post) }}"
                       class="group rounded-2xl border border-slate-200 bg-slate-900 overflow-hidden shadow-sm hover:shadow-lg hover:bg-slate-800 transition duration-300">
                        <div class="relative h-40">
                            <img class="absolute inset-0 h-full w-full object-cover opacity-90"
                                 src="{{ $post->picture?->url }}"
                                 alt="{{ $post->title }}"
                                 title="{{ $post->title }}"
                            />
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>

                            <div class="absolute top-3 right-3 flex items-center gap-2">
                                @foreach($post->categories as $category)
                                    <span
                                        class="inline-flex items-center gap-2 rounded-2xl bg-white/90 px-3 py-1 text-xs font-extrabold text-slate-900">
                                        {{ $category->title }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold text-white truncate">
                                        {{ $post->title }}
                                    </div>
                                    <div class="mt-2 text-xs text-white/70 line-clamp-2 leading-6">
                                        {{ $post->excerpt() }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between">
                                <span class="text-xs text-white/60">انتشار: {{ time_ago($post->published_at) }}</span>
                                <span class="inline-flex items-center gap-2 text-xs font-extrabold text-brand-200">
                                    مشاهده
                                    <span class="[&_svg]:w-4 [&_svg]:h-4">
                                        @include('icons.arrow-left-02')
                                    </span>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div
                class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="text-sm text-slate-700 leading-7">
                    {!! get_setting('home', 'videos_description') !!}
                </div>
                <a href="{{ get_setting('home', 'videos_button_url') }}"
                   class="inline-flex items-center gap-2 rounded-2xl bg-white border border-slate-200 px-4 py-2 text-sm font-extrabold text-brand-700 hover:bg-brand-50 transition">
                    {{ get_setting('home', 'videos_button_title') }}
                    <span class="[&_svg]:w-4 [&_svg]:h-4">
                        @include('icons.arrow-left-02')
                    </span>
                </a>
            </div>
        </div>
    </div>
</section>
