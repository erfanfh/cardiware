<div
    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/20">
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3">
              <span
                  class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                  @include('icons.view')
              </span>
            <h2 class="text-base font-extrabold dark:text-slate-100">{{ get_setting('sidebarWidgets', 'populars_title') }}</h2>
        </div>

        <p class="mt-2 text-xs text-slate-500 leading-6 dark:text-slate-400">
            {{ get_setting('sidebarWidgets', 'populars_subtitle') }}
        </p>
    </div>

    <div class="mt-4 space-y-3">
        @foreach(\App\Domain\Content\Models\Post::take(get_setting('sidebarWidgets', 'populars_posts_count'))->get() as $post)
            <a href="{{ route('contents.detail', $post) }}"
               class="flex gap-3 rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition dark:border-slate-700 dark:hover:bg-slate-800">
                <img src="{{ $post->picture?->url }}" alt="{{ $post->title }}"
                     class="h-14 w-14 rounded-2xl object-cover"/>
                <div class="min-w-0">
                    <div class="text-sm font-extrabold leading-6 truncate dark:text-slate-100">
                        {{ $post->title }}
                    </div>
                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        {{ $post->excerpt }}
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
