@extends('front.marketplace.seller.layout')

@section('seller-title', 'Ticket conversation')
@section('seller-heading', $ticket->title)
@section('seller-description', 'Customer conversation about product ' . ($ticket->product?->title ?? 'Store'))

@section('seller-content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('seller.stores.tickets', $store) }}" class="text-xs font-bold text-brand-600 dark:text-brand-300">← Back to tickets</a>
        <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400"><span dir="ltr">#{{ $ticket->number }}</span><span class="rounded-full bg-slate-100 px-3 py-1.5 font-bold dark:bg-slate-800">{{ $ticket->status->label() }}</span></div>
    </div>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-wrap gap-x-5 gap-y-1 border-b border-slate-100 px-5 py-4 text-xs text-slate-500 sm:px-7 dark:border-slate-800 dark:text-slate-400">
            <span class="min-w-0 break-words">Product: <strong class="text-slate-800 dark:text-slate-200">{{ $ticket->product?->title ?? 'Unknown' }}</strong></span>
            <span class="min-w-0 break-words">Customer: <strong class="text-slate-800 dark:text-slate-200">{{ $ticket->user?->display_name ?? 'User' }}</strong></span>
        </div>
        <div class="max-h-[680px] space-y-5 overflow-y-auto bg-slate-50/60 px-4 py-6 sm:px-7 dark:bg-slate-950/30">
            @foreach($ticket->messages as $message)
                @php($fromSeller = $message->isFromSupport())
                <div class="flex {{ $fromSeller ? 'justify-start' : 'justify-end' }}">
                    <div class="min-w-0 max-w-[90%] sm:max-w-[75%]">
                        <div class="mb-1.5 text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ $fromSeller ? 'Support reply · ' . ($message->user?->display_name ?? 'Support') : ($message->isSystemMessage() ? 'System' : ($ticket->user?->display_name ?? 'Customer')) }}</div>
                        <div class="rounded-2xl px-4 py-3 text-sm leading-8 shadow-sm {{ $fromSeller ? 'rounded-tr-sm bg-brand-600 text-white' : 'rounded-tl-sm border border-slate-200 bg-white text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100' }}">
                            <div class="whitespace-pre-line break-words">{{ $message->body }}</div>
                            <x-front.ticket-attachments :message="$message" :tone="$fromSeller ? 'dark' : 'light'" :thumbnail-class="$fromSeller ? '' : 'dark:border-slate-600 dark:focus:ring-brand-500/20'" :open-in-new-tab="true" />
                        </div>
                        <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">{{ (($message->created_at) ? \Illuminate\Support\Carbon::parse($message->created_at)->format('Y/m/d H:i') : '') }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        @if(session('success'))<div class="mx-5 mt-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">{{ session('success') }}</div>@endif

        @if($ticket->canReceiveReply() && $store->sellerCan('reply_tickets'))
            <form action="{{ route('seller.stores.tickets.reply', [$store, $ticket]) }}" method="post" enctype="multipart/form-data" class="border-t border-slate-100 bg-white p-5 sm:p-7 dark:border-slate-800 dark:bg-slate-900">
                @csrf
                <label for="seller-ticket-message" class="mb-2 block text-sm font-black">Reply to customer</label>
                <div class="grid grid-cols-[minmax(0,1fr)_auto] gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-2 transition focus-within:border-brand-300 focus-within:bg-white focus-within:ring-4 focus-within:ring-brand-100/60 dark:border-slate-700 dark:bg-slate-800/70 dark:focus-within:border-brand-500 dark:focus-within:bg-slate-800 dark:focus-within:ring-brand-500/10">
                    <x-front.ticket-image-upload mode="composer" layout="stacked-actions" />

                    <textarea
                        id="seller-ticket-message"
                        name="message"
                        rows="3"
                        required
                        minlength="2"
                        maxlength="5000"
                        placeholder="Write your reply here…"
                        class="col-start-1 row-span-2 row-start-1 min-h-[6.5rem] min-w-0 resize-none border-0 bg-transparent px-2 py-2 text-sm font-bold leading-7 text-slate-800 outline-none placeholder:text-slate-400 focus:ring-0 dark:text-white dark:placeholder:text-slate-500"
                    >{{ old('message') }}</textarea>

                    <button type="submit" class="col-start-2 row-start-2 grid h-12 w-12 shrink-0 cursor-pointer place-items-center rounded-2xl bg-brand-600 text-white shadow-sm shadow-brand-500/20 transition hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-100 dark:focus:ring-brand-500/20" aria-label="Send reply" title="Send reply">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M21 12 3 20l3.2-8L3 4l18 8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M6.5 12H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                @error('message')<p class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-300">{{ $message }}</p>@enderror
            </form>
        @else
            <div class="border-t border-slate-100 p-5 text-center text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">{{ $store->sellerCan('reply_tickets') ? 'This ticket is closed and cannot receive replies.' : 'Your ticket access is read-only.' }}</div>
        @endif
    </section>

@endsection
