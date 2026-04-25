<x-front.cart>
    <x-front.cart-title
        title="نوشته‌های مرتبط"
        iconName="arrow-reload-horizontal"
    />

    <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
        @if(isset($relatedPosts[0]))
            <a href="{{ route('contents.detail', $relatedPosts[0]) }}"
               class="h-56 rounded-2xl border border-slate-200 bg-slate-900 overflow-hidden shadow-sm hover:shadow-lg transition group dark:border-slate-700 dark:shadow-black/20 dark:hover:shadow-black/30">
                <div class="relative h-full">
                    <img src="{{ $relatedPosts[0]->picture?->url }}" alt=""
                         class="h-full w-full object-cover opacity-80 group-hover:scale-[1.02] transition duration-500">
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/35 to-transparent"></div>
                    <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                        @foreach($relatedPosts[0]->tags as $tag)
                            <span
                                class="text-[11px] font-extrabold text-slate-900 bg-white/90 px-2 py-1 rounded-xl dark:bg-slate-950/90 dark:text-slate-100">
                            {{ $tag->title }}
                        </span>
                        @endforeach
                        @foreach($relatedPosts[0]->categories as $category)
                            <span
                                class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">
                            {{ $category->title }}
                        </span>
                        @endforeach
                    </div>
                    <div class="absolute bottom-3 right-3 left-3">
                        <div class="text-white font-extrabold text-base leading-7 line-clamp-2">
                            {{ $relatedPosts[0]->title }}
                        </div>
                    </div>
                </div>
            </a>
        @endif

        <div class="flex flex-col justify-between gap-3">
            @foreach($relatedPosts->skip(1)->all() as $post)
                <a href="{{ route('contents.detail', $post) }}"
                   class="flex gap-3 rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition dark:border-slate-700 dark:hover:bg-slate-800">
                    <img src="{{ $post->picture?->url }}" alt=""
                         class="h-16 w-16 rounded-2xl object-cover border border-slate-200 dark:border-slate-700">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            @foreach($post->categories as $category)
                                <span
                                    class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $category->title }}
                                </span>
                            @endforeach
                        </div>
                        <div class="mt-2 text-sm font-extrabold leading-6 truncate dark:text-slate-100">
                            {{ $post->title }}
                        </div>
                        <div class="mt-1 text-xs text-slate-500 truncate dark:text-slate-400">
                            {{ $post->excerpt() }}
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-front.cart>
