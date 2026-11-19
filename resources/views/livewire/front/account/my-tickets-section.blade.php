<div id="my-tickets-section" class="mt-4 scroll-mt-36 sm:mt-5">
    <div wire:loading.class="opacity-40" class="transition">
        @forelse($this->tickets as $ticket)
            <article
                wire:key="ticket-{{ $ticket->id }}"
                class="group relative mb-3 overflow-hidden rounded-2xl border shadow-sm transition hover:-translate-y-0.5 hover:shadow-xl sm:mb-4 sm:rounded-3xl {{ $this->isVipMember
                    ? 'border-amber-300 bg-gradient-to-l from-amber-50/80 to-white hover:border-amber-400 hover:shadow-amber-100/50 dark:border-amber-500/30 dark:from-amber-500/10 dark:to-slate-800/70 dark:shadow-black/20 dark:hover:border-amber-500/50'
                    : 'border-slate-200 bg-white hover:border-brand-200 hover:shadow-brand-100/40 dark:border-slate-700 dark:bg-slate-800/70 dark:shadow-black/20 dark:hover:border-brand-500/30 dark:hover:shadow-black/30' }}"
            >
                <a href="{{ $this->ticketUrl($ticket) }}" class="block p-3 sm:p-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="rounded-xl px-2 py-1 text-[10px] font-extrabold sm:text-[11px] {{ $this->statusClass($ticket) }}">
                                    {{ $this->statusLabel($ticket) }}
                                </span>

                                <span
                                    class="rounded-xl border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-extrabold text-slate-600 sm:text-[11px] dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300">
                                    تیکت #{{ $ticket->number }}
                                </span>

                                @if($ticket->department)
                                    <span
                                        class="rounded-xl border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-extrabold text-slate-600 sm:text-[11px] dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300">
                                        {{ $ticket->department->title }}
                                    </span>
                                @endif

                                @if($this->isVipMember)
                                    <span
                                        class="inline-flex items-center gap-1 rounded-xl border border-amber-300 bg-gradient-to-l from-amber-100 to-amber-50 px-2 py-1 text-[10px] font-extrabold text-amber-800 sm:text-[11px] dark:border-amber-500/40 dark:from-amber-500/20 dark:to-amber-500/5 dark:text-amber-300">
                                        👑 پشتیبانی ویژه VIP
                                    </span>
                                @endif

                                @if($ticket->isUnderReview())
                                    <span
                                        class="inline-flex items-center gap-1 rounded-xl border border-sky-300 bg-sky-100 px-2 py-1 text-[10px] font-extrabold text-sky-800 sm:text-[11px] dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-300">
                                        🔍 در حال بررسی
                                    </span>
                                @endif

                                @if($ticket->product)
                                    <span
                                        class="rounded-xl border border-brand-100 bg-brand-50 px-2 py-1 text-[10px] font-extrabold text-brand-700 sm:text-[11px] dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                        {{ $ticket->product->title }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 flex min-w-0 items-start gap-3">
                                <div
                                    class="hidden h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-600 ring-1 ring-brand-100 sm:grid dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/20">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                        <path
                                            d="M8 10h8M8 14h5M21 12c0 4.418-4.03 8-9 8a10.4 10.4 0 0 1-4.064-.816L3 20l1.26-3.36A7.57 7.57 0 0 1 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h3 class="line-clamp-1 text-sm font-black text-slate-900 sm:text-base dark:text-white">
                                        {{ $ticket->title }}
                                    </h3>

                                    <div
                                        class="mt-2 line-clamp-2 text-xs font-bold leading-6 text-slate-500 sm:text-sm sm:leading-7 dark:text-slate-400">
                                        <span class="font-black {{ $this->latestMessageSenderClass($ticket) }}">
                                            {{ $this->latestMessageSenderLabel($ticket) }}:
                                        </span>

                                        {{ $this->latestMessagePreview($ticket) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 lg:flex-col lg:items-end">
                            <div class="text-left">
                                <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500">
                                    آخرین بروزرسانی
                                </div>

                                <div class="mt-1 text-xs font-black text-slate-700 dark:text-slate-200">
                                    {{ $this->latestActivityLabel($ticket) }}
                                </div>
                            </div>

                            <div
                                class="inline-flex h-10 items-center justify-center gap-1 rounded-2xl border border-brand-100 bg-brand-50 px-3 text-xs font-extrabold text-brand-700 transition group-hover:bg-brand-600 group-hover:text-white dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 dark:group-hover:bg-brand-500 dark:group-hover:text-white">
                                ورود به گفتگو

                                <svg class="h-4 w-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M15 18 9 12l6-6"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>
            </article>
        @empty
            @include('front.account.pages.tickets.partials.empty-state')
        @endforelse

        @if($this->tickets->hasPages())
            <div class="pt-2 dark:text-slate-300">
                {{ $this->tickets->links(data: ['scrollTo' => '#my-tickets-section']) }}
            </div>
        @endif
    </div>
</div>
