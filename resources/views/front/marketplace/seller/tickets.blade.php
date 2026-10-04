@extends('front.marketplace.seller.layout')

@section('seller-title', 'تیکت‌ها')
@section('seller-heading', 'تیکت‌های محصولات')
@section('seller-description', 'درخواست‌های مشتریان دربارهٔ محصولات فروشگاه را بررسی و پاسخ دهید.')

@section('seller-content')
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 p-5 sm:p-7 dark:border-slate-800">
            <div><h2 class="text-lg font-black">گفتگوها</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format($tickets->total()) }} تیکت</p></div>
            <div class="flex flex-wrap gap-2">
                @foreach(['all' => 'همه', 'open' => 'نیازمند پاسخ', 'answered' => 'پاسخ‌داده‌شده', 'closed' => 'بسته‌شده'] as $key => $label)
                    <a href="{{ route('seller.stores.tickets', [$store, 'status' => $key]) }}" class="rounded-xl px-3 py-2 text-xs font-bold {{ $status === $key ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($tickets as $ticket)
                <a href="{{ route('seller.stores.tickets.show', [$store, $ticket]) }}" class="flex flex-wrap items-center justify-between gap-4 p-5 transition hover:bg-slate-50 sm:p-6 dark:hover:bg-slate-800/50">
                    <div class="min-w-0">
                        <div class="truncate text-sm font-black">{{ $ticket->title }}</div>
                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400"><span>{{ $ticket->product?->title ?? 'محصول' }}</span><span>{{ $ticket->user?->display_name ?? 'کاربر' }}</span><span dir="ltr">#{{ $ticket->number }}</span></div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3"><span class="rounded-full px-3 py-1.5 text-xs font-bold {{ $ticket->status->value === 'open' ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">{{ $ticket->status->label() }}</span><span class="text-xs text-slate-500 dark:text-slate-400">{{ $ticket->last_message_at ? jalali_date($ticket->last_message_at, 'Y/m/d') : '' }}</span></div>
                </a>
            @empty
                <p class="p-10 text-center text-sm text-slate-500 dark:text-slate-400">تیکتی در این بخش وجود ندارد.</p>
            @endforelse
        </div>
        @if($tickets->hasPages())<div class="border-t border-slate-100 p-5 dark:border-slate-800">{{ $tickets->links() }}</div>@endif
    </section>
@endsection
