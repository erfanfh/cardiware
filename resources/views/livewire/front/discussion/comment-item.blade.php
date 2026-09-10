<article
    id="discussion-comment-{{ $this->comment->id }}"
    x-data="{
        replyOpen: @entangle('replyFormOpen').live,
        openReplyForm() {
            this.replyOpen = true;

            this.$nextTick(() => {
                const form = this.$refs.replyForm;
                const textarea = this.$refs.replyTextarea;

                if (form) {
                    form.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                }

                if (textarea) {
                    textarea.focus();
                }
            });
        },
        closeReplyForm() {
            this.replyOpen = false;
        }
    }"
    @class([
        'group rounded-2xl border p-3.5 transition sm:p-5',
        'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700' => ! $this->isPendingVisibleToCurrentViewer(),
        'border-amber-200 bg-amber-50/40 dark:border-amber-500/20 dark:bg-amber-500/10' => $this->isPendingVisibleToCurrentViewer(),
        'mt-3' => $level > 0,
    ])
>
    <div class="flex items-start gap-3 sm:hidden">
        <div
            class="grid h-10 w-10 shrink-0 place-items-center overflow-hidden rounded-2xl border border-slate-200 text-sm font-black dark:border-slate-700 {{ $this->avatarColor() }}"
        >
            @if($this->comment->user?->avatar)
                <img
                    src="{{ $this->comment->user->avatar->url }}"
                    class="h-full w-full object-cover"
                    alt="{{ $this->comment->display_name }}"
                >
            @else
                <span>{{ $this->avatarText() }}</span>
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <div class="max-w-full truncate text-sm font-black text-slate-900 dark:text-slate-100">
                    {{ $this->comment->display_name }}
                </div>

                @foreach($this->authorBadges as $badge)
                    <x-front.discussion.comment-author-badge
                        :badge="$badge"
                    />
                @endforeach

                @if($this->comment->is_pinned)
                    <x-front.discussion.comment-pinned-badge/>
                @endif

                <span class="h-1 w-1 shrink-0 rounded-full bg-slate-300 dark:bg-slate-600"></span>

                <span class="shrink-0 text-xs text-slate-500 dark:text-slate-400">
                    {{ time_ago($this->comment->created_at) }}
                </span>
            </div>

            @if($this->comment->rating_stars > 0)
                <div class="mt-2 flex items-center gap-0.5">
                    @for($n = 1; $n <= 5; $n++)
                        <svg
                            viewBox="0 0 20 20"
                            class="h-4 w-4 {{ $this->comment->rating_stars >= $n ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700' }}"
                        >
                            <path
                                fill="currentColor"
                                d="M10 15.27l-5.18 2.73 0.99-5.82L1.62 7.9l5.84-.85L10 1.75l2.54 5.3 5.84.85-4.19 4.28.99 5.82z"
                            />
                        </svg>
                    @endfor
                </div>
            @endif
        </div>
    </div>

    <div class="hidden gap-4 sm:flex">
        <div
            class="grid h-11 w-11 shrink-0 place-items-center overflow-hidden rounded-2xl border border-slate-200 text-sm font-black dark:border-slate-700 {{ $this->avatarColor() }}"
        >
            @if($this->comment->user?->avatar)
                <img
                    src="{{ $this->comment->user->avatar->url }}"
                    class="h-full w-full object-cover"
                    alt="{{ $this->comment->display_name }}"
                >
            @else
                <span>{{ $this->avatarText() }}</span>
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <div class="max-w-full truncate text-sm font-black text-slate-900 dark:text-slate-100">
                            {{ $this->comment->display_name }}
                        </div>

                        @foreach($this->authorBadges as $badge)
                            <x-front.discussion.comment-author-badge
                                :badge="$badge"
                            />
                        @endforeach

                        @if($this->comment->is_pinned)
                            <x-front.discussion.comment-pinned-badge/>
                        @endif

                        <span class="h-1 w-1 shrink-0 rounded-full bg-slate-300 dark:bg-slate-600"></span>

                        <span class="shrink-0 text-xs text-slate-500 dark:text-slate-400">
                            {{ time_ago($this->comment->created_at) }}
                        </span>
                    </div>

                    @if($this->comment->rating_stars > 0)
                        <div class="mt-2 flex items-center gap-0.5">
                            @for($n = 1; $n <= 5; $n++)
                                <svg
                                    viewBox="0 0 20 20"
                                    class="h-4 w-4 {{ $this->comment->rating_stars >= $n ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700' }}"
                                >
                                    <path
                                        fill="currentColor"
                                        d="M10 15.27l-5.18 2.73 0.99-5.82L1.62 7.9l5.84-.85L10 1.75l2.54 5.3 5.84.85-4.19 4.28.99 5.82z"
                                    />
                                </svg>
                            @endfor
                        </div>
                    @endif
                </div>

                @if($allowReplySubmission)
                    <button
                        @click="openReplyForm()"
                        type="button"
                        class="inline-flex h-10 cursor-pointer items-center justify-center gap-1.5 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-xs font-extrabold text-slate-700 transition hover:border-slate-300 hover:bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-950"
                    >
                        <span>پاسخ</span>
                        <span class="text-slate-400 dark:text-slate-500">
                        @include('icons.arrow-left-double')
                    </span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-4 sm:mt-0 sm:pr-[3.75rem]">
        @if($this->isPendingVisibleToCurrentViewer())
            <div
                class="rounded-2xl mt-3 border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">
                <div class="font-extrabold">دیدگاه شما در انتظار تأیید است.</div>
                <p class="mt-1 text-xs leading-6 text-amber-700 dark:text-amber-300/80">
                    این دیدگاه فقط برای شما نمایش داده می‌شود و پس از بررسی منتشر خواهد شد.
                </p>
            </div>
        @endif

        <div
            class="tinymce-content comment-content mt-3 break-words text-sm leading-8 text-slate-700 dark:text-slate-300 dark:prose-invert">
            {!! $this->renderedContent !!}
        </div>

        @if($this->comment->isApproved())
            <div class="mt-3 flex w-full justify-end">
                <div
                    wire:loading.class="pointer-events-none opacity-60"
                    wire:target="toggleReaction"
                    class="inline-flex items-center gap-0.5 rounded-xl p-0.5 transition
                   dark:border dark:border-slate-700 dark:bg-slate-800/70 dark:shadow-sm dark:shadow-black/10"
                >
                    <button
                        type="button"
                        wire:click="toggleReaction('like')"
                        wire:loading.attr="disabled"
                        wire:target="toggleReaction"
                        aria-label="پسندیدن دیدگاه"
                        aria-pressed="{{ $this->viewerReactionType === 'like' ? 'true' : 'false' }}"
                        title="پسندیدن دیدگاه"
                        @class([
                            'inline-flex h-7 min-w-7 cursor-pointer items-center justify-center gap-1 rounded-lg px-1.5 text-[10px] font-black transition active:scale-95 disabled:cursor-wait',

                            'bg-emerald-500 text-white shadow-sm shadow-emerald-500/20'
                                => $this->viewerReactionType === 'like',

                            'text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 dark:text-slate-400 dark:hover:bg-slate-900 dark:hover:text-emerald-400'
                                => $this->viewerReactionType !== 'like',
                        ])
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            class="h-4 w-4 shrink-0"
                        >
                            <path
                                d="M7.5 10.5v9m0-9L11 3.8c.45-.85 1.5-1.15 2.33-.67.63.37.96 1.1.83 1.82l-.7 3.8H19a2 2 0 0 1 1.95 2.43l-1.5 6.75a2 2 0 0 1-1.95 1.57H7.5m0-9H4.75A1.75 1.75 0 0 0 3 12.25v5.5c0 .97.78 1.75 1.75 1.75H7.5"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>

                        @if($this->comment->likes_count > 0)
                            <span class="leading-none">
                        {{ $this->comment->likes_count }}
                    </span>
                        @endif
                    </button>

                    <button
                        type="button"
                        wire:click="toggleReaction('dislike')"
                        wire:loading.attr="disabled"
                        wire:target="toggleReaction"
                        aria-label="نپسندیدن دیدگاه"
                        aria-pressed="{{ $this->viewerReactionType === 'dislike' ? 'true' : 'false' }}"
                        title="نپسندیدن دیدگاه"
                        @class([
                            'inline-flex h-7 min-w-7 cursor-pointer items-center justify-center gap-1 rounded-lg px-1.5 text-[10px] font-black transition active:scale-95 disabled:cursor-wait',

                            'bg-rose-500 text-white shadow-sm shadow-rose-500/20'
                                => $this->viewerReactionType === 'dislike',

                            'text-slate-500 hover:bg-rose-50 hover:text-rose-600 dark:text-slate-400 dark:hover:bg-slate-900 dark:hover:text-rose-400'
                                => $this->viewerReactionType !== 'dislike',
                        ])
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            class="h-4 w-4 shrink-0"
                        >
                            <path
                                d="M7.5 13.5v-9m0 9L11 20.2c.45.85 1.5 1.15 2.33.67.63-.37.96-1.1.83-1.82l-.7-3.8H19a2 2 0 0 0 1.95-2.43l-1.5-6.75A2 2 0 0 0 17.5 4.5h-10m0 9H4.75A1.75 1.75 0 0 1 3 11.75v-5.5C3 5.28 3.78 4.5 4.75 4.5H7.5"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>

                        @if($this->comment->dislikes_count > 0)
                            <span class="leading-none">
                        {{ $this->comment->dislikes_count }}
                    </span>
                        @endif
                    </button>
                </div>
            </div>
        @endif

        @if($allowReplySubmission)
            <button
                @click="openReplyForm()"
                type="button"
                class="mt-4 inline-flex h-10 w-full cursor-pointer items-center justify-center gap-1.5 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-xs font-extrabold text-slate-700 transition hover:border-slate-300 hover:bg-white sm:hidden dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-950"
            >
                <span>پاسخ</span>
                <span class="text-slate-400 dark:text-slate-500">
                @include('icons.arrow-left-double')
            </span>
            </button>
        @endif

        @if($showReplies && $this->hasVisibleReplies)
            @if(! $repliesOpen)
                <div class="mt-4">
                    <div
                        class="overflow-hidden rounded-2xl border border-dashed border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 dark:border-slate-700 dark:from-slate-900 dark:via-slate-950 dark:to-slate-900">
                        <div
                            class="flex flex-col gap-3 px-3.5 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="inline-flex h-7 min-w-7 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white px-2 text-[11px] font-extrabold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                                        {{ $this->visibleRepliesCount }}
                                    </span>

                                    <div
                                        class="min-w-0 text-xs font-extrabold leading-6 text-slate-800 dark:text-slate-200">
                                        @if($this->approvedRepliesCount > 0 && $this->pendingVisibleRepliesCount > 0)
                                            {{ $this->visibleRepliesCount }} پاسخ برای این دیدگاه قابل مشاهده است
                                        @elseif($this->approvedRepliesCount > 0)
                                            {{ $this->approvedRepliesCount === 1
                                                ? 'یک پاسخ برای این دیدگاه ثبت شده است'
                                                : $this->approvedRepliesCount . ' پاسخ برای این دیدگاه ثبت شده است' }}
                                        @else
                                            {{ $this->pendingVisibleRepliesCount === 1
                                                ? 'پاسخ شما برای این دیدگاه در انتظار تأیید است'
                                                : $this->pendingVisibleRepliesCount . ' پاسخ شما برای این دیدگاه در انتظار تأیید است' }}
                                        @endif
                                    </div>
                                </div>

                                <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                    ادامه این گفتگو را مشاهده کنید.
                                </p>
                            </div>

                            <div class="shrink-0 max-sm:w-full">
                                <button
                                    wire:click="toggleReplies"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleReplies"
                                    type="button"
                                    class="inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-xs font-extrabold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-70 max-sm:w-full dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-800"
                                >
                                    <svg
                                        wire:loading
                                        wire:target="toggleReplies"
                                        class="h-4 w-4 shrink-0 animate-spin"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                    >
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"
                                                stroke-width="4"></circle>
                                        <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"
                                              stroke-linecap="round"></path>
                                    </svg>

                                    <span wire:loading.remove wire:target="toggleReplies">نمایش پاسخ‌ها</span>
                                    <span wire:loading wire:target="toggleReplies">در حال بارگذاری...</span>

                                    <span
                                        class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-slate-100 px-1.5 text-[11px] text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        {{ $this->visibleRepliesCount }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($repliesOpen)
                @if($repliesLoading)
                    <div class="mt-4 w-full border-r-2 border-slate-100 pr-2 sm:pr-4 dark:border-slate-800">
                        <div class="w-full space-y-3">
                            @for($i = 0; $i < 2; $i++)
                                <div
                                    class="w-full rounded-2xl border border-slate-200 bg-white p-3.5 sm:p-4 dark:border-slate-700 dark:bg-slate-900">
                                    <div class="animate-pulse">
                                        <div class="flex w-full gap-3">
                                            <div
                                                class="h-10 w-10 shrink-0 rounded-2xl bg-slate-200 dark:bg-slate-800"></div>

                                            <div class="min-w-0 w-full flex-1">
                                                <div class="flex w-full items-center gap-2">
                                                    <div
                                                        class="h-4 w-24 rounded bg-slate-200 sm:w-28 dark:bg-slate-800"></div>
                                                    <div
                                                        class="h-1 w-1 rounded-full bg-slate-200 dark:bg-slate-800"></div>
                                                    <div
                                                        class="h-3 w-14 rounded bg-slate-100 sm:w-16 dark:bg-slate-800"></div>
                                                </div>

                                                <div class="mt-3 w-full space-y-2">
                                                    <div
                                                        class="h-3 w-full rounded bg-slate-100 dark:bg-slate-800"></div>
                                                    <div
                                                        class="h-3 w-full rounded bg-slate-100 dark:bg-slate-800"></div>
                                                    <div class="h-3 w-2/3 rounded bg-slate-100 dark:bg-slate-800"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                @endif

                @if(! $repliesLoading && $this->replies->isNotEmpty())
                    <div
                        x-data="{ visible: false }"
                        x-init="setTimeout(() => visible = true, 10)"
                        x-show="visible"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="mt-4 w-full border-r-2 border-slate-100 pr-2 sm:pr-4 dark:border-slate-800"
                    >
                        <div class="w-full space-y-3">
                            @foreach($this->replies as $reply)
                                <livewire:front.discussion.comment-item
                                    :comment-id="$reply->id"
                                    :commentable-type="$this->commentableType"
                                    :commentable-id="$this->commentableId"
                                    :level="$level + 1"
                                    :show-replies="$showReplies"
                                    :allow-reply-submission="$allowReplySubmission"
                                    :key="'discussion-reply-'.$reply->id.'-parent-'.$this->comment->id.'-show-'.(int) $showReplies.'-submit-'.(int) $allowReplySubmission"
                                />
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        @endif

        @if($allowReplySubmission)
            <div
                x-show="replyOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-1"
                x-ref="replyForm"
                class="relative mt-4 scroll-mt-40 rounded-[1.25rem] border border-slate-200 bg-slate-50/80 p-3.5 sm:p-4 dark:border-slate-700 dark:bg-slate-950/80"
            >
                <div
                    wire:loading.flex
                    wire:target="submitReply"
                    class="absolute inset-0 z-10 items-center justify-center rounded-[1.25rem] bg-white/55 px-3 backdrop-blur-[1px] dark:bg-slate-950/65"
                >
                    <div
                        class="inline-flex max-w-full items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-extrabold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:shadow-black/20">
                        <svg class="h-4 w-4 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"
                                    stroke-width="4"></circle>
                            <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"
                                  stroke-linecap="round"></path>
                        </svg>
                        <span class="truncate">در حال ارسال پاسخ...</span>
                    </div>
                </div>

                <form wire:submit.prevent="submitReply" wire:loading.class="pointer-events-none opacity-60"
                      wire:target="submitReply">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0 text-sm font-black leading-7 text-slate-800 dark:text-slate-200">
                            پاسخ به
                            <span class="break-words text-brand-600 dark:text-brand-300">
                            {{ $this->comment->display_name }}
                        </span>
                        </div>

                        <button
                            @click="closeReplyForm()"
                            type="button"
                            class="grid h-9 w-9 shrink-0 cursor-pointer place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                        >
                            ×
                        </button>
                    </div>

                    @if($replyGeneralError)
                        <div
                            class="mt-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold leading-7 text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                            {{ $replyGeneralError }}
                        </div>
                    @endif

                    @if($replySuccessMessage)
                        <div
                            class="mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold leading-7 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                            {{ $replySuccessMessage }}
                        </div>
                    @endif

                    @guest
                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <input
                                    wire:model.defer="replyAuthorName"
                                    wire:loading.attr="disabled"
                                    wire:target="submitReply"
                                    type="text"
                                    class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 disabled:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-brand-500/60 dark:focus:ring-brand-500/10 dark:disabled:bg-slate-800"
                                    placeholder="نام شما (الزامی)"
                                >
                                @error('replyAuthorName')
                                <div class="mt-2 text-xs font-bold text-red-600 dark:text-red-400">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <input
                                    wire:model.defer="replyAuthorEmail"
                                    wire:loading.attr="disabled"
                                    wire:target="submitReply"
                                    type="email"
                                    class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 disabled:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-brand-500/60 dark:focus:ring-brand-500/10 dark:disabled:bg-slate-800"
                                    placeholder="ایمیل شما (الزامی)"
                                >
                                @error('replyAuthorEmail')
                                <div class="mt-2 text-xs font-bold text-red-600 dark:text-red-400">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endguest

                    <textarea
                        x-ref="replyTextarea"
                        wire:model.defer="replyContent"
                        wire:loading.attr="disabled"
                        wire:target="submitReply"
                        rows="3"
                        class="mt-3 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm leading-7 outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 disabled:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-brand-500/60 dark:focus:ring-brand-500/10 dark:disabled:bg-slate-800"
                        placeholder="پاسخ شما…"
                    ></textarea>

                    @error('replyContent')
                    <div class="mt-2 text-xs font-bold text-red-600 dark:text-red-400">{{ $message }}</div>
                    @enderror

                    <div class="mt-3 flex items-center justify-end gap-2 max-sm:grid max-sm:grid-cols-2">
                        <button
                            @click="closeReplyForm()"
                            wire:loading.attr="disabled"
                            wire:target="submitReply"
                            type="button"
                            class="inline-flex h-9 cursor-pointer items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-xs font-extrabold text-slate-700 transition hover:bg-slate-50 hover:opacity-95 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-60 max-sm:w-full dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        >
                            لغو
                        </button>

                        <button
                            wire:loading.attr="disabled"
                            wire:target="submitReply"
                            type="submit"
                            class="inline-flex h-9 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-4 text-xs font-extrabold text-white transition hover:bg-brand-600 hover:opacity-95 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70 max-sm:w-full dark:hover:bg-brand-400"
                        >
                            <svg wire:loading wire:target="submitReply" class="h-4 w-4 shrink-0 animate-spin"
                                 viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"
                                        stroke-width="4"></circle>
                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"
                                      stroke-linecap="round"></path>
                            </svg>

                            <span wire:loading.remove wire:target="submitReply">ارسال پاسخ</span>
                            <span wire:loading wire:target="submitReply" class="truncate">در حال ارسال...</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</article>
