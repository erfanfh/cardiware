@php
    $latestCommentsWidget = app(\App\Application\System\Actions\Sidebar\GetSidebarLatestCommentsAction::class)->execute();
@endphp

<x-front.sidebar.sidebar-item
    :title="$latestCommentsWidget['title']"
    :subtitle="$latestCommentsWidget['subtitle']"
    iconName="comment-01"
>
    <div class="space-y-3">
        @foreach($latestCommentsWidget['comments'] as $comment)
            <a href="{{ $comment->commentable->url }}"
               class="block rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition dark:border-slate-700 dark:hover:bg-slate-800">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm font-extrabold truncate dark:text-slate-100">
                        User: {{ $comment->user?->display_name ?? $comment->author_name }}
                    </div>

                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        {{ time_ago($comment->created_at) }}
                    </div>
                </div>

                <div class="mt-1 text-xs text-slate-500 leading-6 line-clamp-2 dark:text-slate-400">
                    {{ $comment->plain_content }}
                </div>

                <div
                    class="mt-2 text-[0.7rem] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl inline-block dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                    <span class="line-clamp-1">
                        {{ $comment->commentable->title }}
                    </span>
                </div>
            </a>
        @endforeach
    </div>
</x-front.sidebar.sidebar-item>
