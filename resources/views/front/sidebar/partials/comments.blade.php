@php
    $comments = \App\Domain\Discussion\Models\Comment::approved()
        ->where('commentable_type', \App\Domain\Content\Models\Post::class)
        ->limit(get_setting('sidebarWidgets', 'comments_count') ?: 5)
        ->with(['commentable', 'user'])
        ->get();
@endphp

<x-front.sidebar.sidebar-item
    :title="get_setting('sidebarWidgets', 'comments_title')"
    :subtitle="get_setting('sidebarWidgets', 'comments_subtitle')"
    iconName="comment-01"
>
    <div class="space-y-3">
        @foreach($comments as $comment)
            <a href="{{ $comment->commentable->url }}"
               class="block rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm font-extrabold truncate">
                        کاربر: {{ $comment->user?->display_name ?? $comment->author_name }}
                    </div>
                    <div class="text-xs text-slate-500">{{ time_ago($comment->created_at) }}</div>
                </div>
                <div class="mt-1 text-xs text-slate-500 leading-6 line-clamp-2">
                    {{ $comment->content }}
                </div>
                <div
                    class="mt-2 text-[0.7rem] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl inline-block">
                    <span class="line-clamp-1">{{ $comment->commentable->title }}</span>
                </div>
            </a>
        @endforeach
    </div>
</x-front.sidebar.sidebar-item>
