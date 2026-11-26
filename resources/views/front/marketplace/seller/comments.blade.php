@extends('front.marketplace.seller.layout')

@section('seller-title', 'Comments')
@section('seller-heading', 'Product comments')
@section('seller-description', 'Track customer feedback on this store’s products.')

@section('seller-content')
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div><h2 class="text-lg font-black">Comments</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format($comments->total()) }} items</p></div>
            <div class="flex flex-wrap gap-2">
                @foreach(['all' => 'All', 'approved' => 'Approved', 'pending' => 'Pending', 'rejected' => 'Rejected'] as $key => $label)
                    <a href="{{ route('seller.stores.comments', [$store, 'status' => $key]) }}" class="rounded-xl px-3 py-2 text-xs font-bold {{ $status === $key ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
        <div class="mt-5 grid gap-3">
            @forelse($comments as $comment)
                @php($state = match ($comment->status->value) { 'approved' => 'Approved', 'pending' => 'Pending', default => 'Rejected' })
                <article id="comment-{{ $comment->id }}" class="scroll-mt-28 rounded-2xl border border-slate-100 bg-slate-50/60 p-5 dark:border-slate-800 dark:bg-slate-800/40">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0 break-words text-sm font-black">{{ $comment->display_name }} <span class="mx-1 font-normal text-slate-300 dark:text-slate-500">/</span> <span class="text-brand-600 dark:text-brand-300">{{ $productNames[$comment->commentable_id] ?? 'Product' }}</span></div>
                        <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-bold text-slate-600 dark:bg-slate-700 dark:text-slate-200">{{ $state }}</span>
                    </div>
                    <p class="mt-3 whitespace-pre-line break-words text-sm leading-8 text-slate-700 dark:text-slate-300">{{ $comment->plain_content }}</p>
                    <div class="mt-3 flex flex-wrap gap-4 text-xs text-slate-500 dark:text-slate-400">
                        @if($comment->rating_percent !== null)<span>points: {{ $comment->rating_stars }} out of 5</span>@endif
                        <span>{{ (($comment->created_at) ? \Illuminate\Support\Carbon::parse($comment->created_at)->format('Y/m/d H:i') : '') }}</span>
                    </div>
                </article>
            @empty
                <p class="py-10 text-center text-sm text-slate-500 dark:text-slate-400">No comments in this section.</p>
            @endforelse
        </div>
        @if($comments->hasPages())<div class="mt-5">{{ $comments->links() }}</div>@endif
    </section>
@endsection
