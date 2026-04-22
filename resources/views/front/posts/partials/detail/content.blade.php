<article class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

    <div class="p-5 pb-0 sm:p-6 sm:pb-0">
        <div class="text-center">
            <div class="relative inline-block overflow-hidden rounded-2xl border border-slate-200">
                <img
                    src="{{ $post->picture?->url }}"
                    alt="{{ $post->title }}"
                    title="{{ $post->title }}"
                    class="block max-w-full h-auto"
                />

                <div class="absolute inset-0 bg-gradient-to-t from-black/0 via-black/30 to-transparent"></div>

                <div class="absolute top-4 right-4 left-4 flex flex-wrap justify-start items-center gap-2">
                    @php $post->categories @endphp
                    @foreach($post->categories as $category)
                        <a href="{{ route('posts.categories', $category) }}"
                           class="text-xs font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">
                            {{ $category->title }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- body -->
    <div class="p-5 sm:p-6">
        <div class="tinymce-content">
            {!! $post->body !!}
        </div>

        @include('front.posts.partials.detail.download-box')

        @if($post->tags->isNotEmpty())
            <div class="mt-6 border-t border-slate-200 pt-5 flex flex-wrap items-center gap-2">
                <span class="text-xs text-slate-500">برچسب‌ها:</span>
                @foreach($post->tags as $tag)
                    <a href="{{ route('posts.tags', $tag) }}"
                       class="text-[0.7rem] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl hover:bg-slate-50 transition">
                        {{ $tag->title }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</article>
