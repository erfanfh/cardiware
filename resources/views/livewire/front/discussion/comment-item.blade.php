<article
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
        'group rounded-2xl border p-4 transition sm:p-5',
        'border-slate-200 bg-white hover:border-slate-300' => ! $this->isPendingVisibleToCurrentViewer(),
        'border-amber-200 bg-amber-50/40' => $this->isPendingVisibleToCurrentViewer(),
        'mt-3' => $level > 0,
    ])
>
    <div class="flex gap-3 sm:gap-4">
        {{-- Avatar --}}
        <div
            class="grid h-11 w-11 shrink-0 place-items-center overflow-hidden rounded-2xl border border-slate-200 text-sm font-black {{ $this->avatarColor() }}"
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
            {{-- Header --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <div class="truncate text-sm font-black text-slate-900">
                            {{ $this->comment->display_name }}
                        </div>

                        <span class="h-1 w-1 rounded-full bg-slate-300"></span>

                        <span class="text-xs text-slate-500">
                            {{ time_ago($this->comment->created_at) }}
                        </span>
                    </div>

                    @if($this->comment->rating_stars > 0)
                        <div class="mt-2 flex items-center gap-0.5">
                            @for($n = 1; $n <= 5; $n++)
                                <svg
                                    viewBox="0 0 20 20"
                                    class="h-4 w-4 {{ $this->comment->rating_stars >= $n ? 'text-amber-400' : 'text-slate-200' }}"
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

                <div class="flex items-center gap-2 self-start">
                    <button
                        @click="openReplyForm()"
                        type="button"
                        class="inline-flex h-10 cursor-pointer items-center gap-1.5 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-xs font-extrabold text-slate-700 transition hover:border-slate-300 hover:bg-white"
                    >
                        <span>پاسخ</span>

                        <span class="text-slate-400">
                            @include('icons.arrow-left-double')
                        </span>
                    </button>
                </div>
            </div>

            @if($this->isPendingVisibleToCurrentViewer())
                <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <div class="font-extrabold">دیدگاه شما در انتظار تأیید است.</div>
                    <p class="mt-1 text-xs leading-6 text-amber-700">
                        این دیدگاه فقط برای شما نمایش داده می‌شود و پس از بررسی منتشر خواهد شد.
                    </p>
                </div>
            @endif

            {{-- Content --}}
            <div class="tinymce-content mt-3 text-sm leading-8 text-slate-700">
                {!! $this->comment->safe_content !!}
            </div>

            {{-- Thread CTA / Replies --}}
            @if($this->comment->approved_replies_count > 0)
                @if(! $repliesOpen)
                    <div class="mt-4">
                        <div
                            class="overflow-hidden rounded-2xl border border-dashed border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50">
                            <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex h-7 min-w-7 items-center justify-center rounded-full border border-slate-200 bg-white px-2 text-[11px] font-extrabold text-slate-700">
                                {{ $this->comment->approved_replies_count }}
                            </span>

                                        <div class="text-xs font-extrabold text-slate-800">
                                            {{ $this->comment->approved_replies_count === 1
                                                ? 'یک پاسخ برای این دیدگاه ثبت شده است'
                                                : $this->comment->approved_replies_count . ' پاسخ برای این دیدگاه ثبت شده است' }}
                                        </div>
                                    </div>

                                    <p class="mt-1 text-xs leading-6 text-slate-500">
                                        ادامه این گفتگو را مشاهده کنید.
                                    </p>
                                </div>

                                <div class="shrink-0">
                                    <button
                                        wire:click="toggleReplies"
                                        wire:loading.attr="disabled"
                                        wire:target="toggleReplies"
                                        type="button"
                                        class="inline-flex h-10 cursor-pointer items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-xs font-extrabold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-70"
                                    >
                                        <svg
                                            wire:loading
                                            wire:target="toggleReplies"
                                            class="h-4 w-4 animate-spin"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                        >
                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                stroke-opacity="0.25"
                                                stroke-width="4"
                                            ></circle>
                                            <path
                                                d="M22 12a10 10 0 0 1-10 10"
                                                stroke="currentColor"
                                                stroke-width="4"
                                                stroke-linecap="round"
                                            ></path>
                                        </svg>

                                        <span wire:loading.remove wire:target="toggleReplies">
                                نمایش پاسخ‌ها
                            </span>

                                        <span wire:loading wire:target="toggleReplies">
                                در حال بارگذاری...
                            </span>

                                        <span
                                            class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-slate-100 px-1.5 text-[11px] text-slate-600">
                                {{ $this->comment->approved_replies_count }}
                            </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($repliesOpen)
                    {{-- Replies loading state --}}
                    @if($repliesLoading)
                        <div class="mt-4 w-full border-r-2 border-slate-100 pr-3 sm:pr-4">
                            <div class="w-full space-y-3">
                                @for($i = 0; $i < 2; $i++)
                                    <div class="w-full rounded-2xl border border-slate-200 bg-white p-4">
                                        <div class="animate-pulse">
                                            <div class="flex w-full gap-3">
                                                <div class="h-10 w-10 shrink-0 rounded-2xl bg-slate-200"></div>

                                                <div class="min-w-0 w-full flex-1">
                                                    <div class="flex w-full items-center gap-2">
                                                        <div class="h-4 w-28 rounded bg-slate-200"></div>
                                                        <div class="h-1 w-1 rounded-full bg-slate-200"></div>
                                                        <div class="h-3 w-16 rounded bg-slate-100"></div>
                                                    </div>

                                                    <div class="mt-3 w-full space-y-2">
                                                        <div class="h-3 w-full rounded bg-slate-100"></div>
                                                        <div class="h-3 w-full rounded bg-slate-100"></div>
                                                        <div class="h-3 w-2/3 rounded bg-slate-100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endif

                    {{-- Replies loaded --}}
                    @if(! $repliesLoading && $this->replies->isNotEmpty())
                        <div
                            x-data="{ visible: false }"
                            x-init="setTimeout(() => visible = true, 10)"
                            x-show="visible"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="mt-4 w-full border-r-2 border-slate-100 pr-3 sm:pr-4"
                        >
                            <div class="w-full">
                                @foreach($this->replies as $reply)
                                    <livewire:front.discussion.comment-item
                                        :comment-id="$reply->id"
                                        :commentable-type="$this->commentableType"
                                        :commentable-id="$this->commentableId"
                                        :level="$level + 1"
                                        :key="'discussion-reply-'.$reply->id.'-parent-'.$this->comment->id"
                                    />
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            @endif

            {{-- Reply form --}}
            <div
                x-show="replyOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-1"
                x-ref="replyForm"
                class="relative mt-4 scroll-mt-40 rounded-[1.25rem] border border-slate-200 bg-slate-50/80 p-4"
            >
                <div
                    wire:loading.flex
                    wire:target="submitReply"
                    class="absolute inset-0 z-10 items-center justify-center rounded-[1.25rem] bg-white/55 backdrop-blur-[1px]"
                >
                    <div
                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-extrabold text-slate-700 shadow-sm">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"
                                    stroke-width="4"></circle>
                            <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"
                                  stroke-linecap="round"></path>
                        </svg>
                        در حال ارسال پاسخ...
                    </div>
                </div>

                <form
                    wire:submit.prevent="submitReply"
                    wire:loading.class="pointer-events-none opacity-60"
                    wire:target="submitReply"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm font-black text-slate-800">
                            پاسخ به <span class="text-brand-600">{{ $this->comment->display_name }}</span>
                        </div>

                        <button
                            @click="closeReplyForm()"
                            type="button"
                            class="grid cursor-pointer h-9 w-9 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-700"
                        >
                            ×
                        </button>
                    </div>

                    @guest
                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <input
                                    wire:model.defer="replyAuthorName"
                                    wire:loading.attr="disabled"
                                    wire:target="submitReply"
                                    type="text"
                                    class="h-11 w-full rounded-2xl border bg-white px-4 text-sm outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 disabled:bg-slate-50 border-slate-200"
                                    placeholder="نام شما (الزامی)"
                                >
                                @error('replyAuthorName')
                                <div class="mt-2 text-xs font-bold text-red-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <input
                                    wire:model.defer="replyAuthorEmail"
                                    wire:loading.attr="disabled"
                                    wire:target="submitReply"
                                    type="email"
                                    class="h-11 w-full rounded-2xl border bg-white px-4 text-sm outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 disabled:bg-slate-50 border-slate-200"
                                    placeholder="ایمیل شما (الزامی)"
                                >
                                @error('replyAuthorEmail')
                                <div class="mt-2 text-xs font-bold text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endguest

                    @if($replyGeneralError)
                        <div
                            class="mt-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
                            {{ $replyGeneralError }}
                        </div>
                    @endif

                    @if($replySuccessMessage)
                        <div
                            class="mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
                            {{ $replySuccessMessage }}
                        </div>
                    @endif

                    <textarea
                        x-ref="replyTextarea"
                        wire:model.defer="replyContent"
                        wire:loading.attr="disabled"
                        wire:target="submitReply"
                        rows="3"
                        class="w-full rounded-2xl border bg-white px-4 py-3 text-sm leading-7 outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 disabled:bg-slate-50 border-slate-200 mt-3"
                        placeholder="پاسخ شما…"
                    ></textarea>

                    @error('replyContent')
                    <div class="mt-2 text-xs font-bold text-red-600">{{ $message }}</div>
                    @enderror

                    <div class="mt-3 flex items-center justify-end gap-2">
                        <button
                            @click="closeReplyForm()"
                            wire:loading.attr="disabled"
                            wire:target="submitReply"
                            type="button"
                            class="inline-flex cursor-pointer h-9 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-xs font-extrabold text-slate-700 transition hover:bg-slate-50 hover:opacity-95 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            لغو
                        </button>

                        <button
                            wire:loading.attr="disabled"
                            wire:target="submitReply"
                            type="submit"
                            class="inline-flex cursor-pointer h-9 items-center justify-center gap-2 rounded-2xl bg-brand-500 px-4 text-xs font-extrabold text-white transition hover:bg-brand-600 hover:opacity-95 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            <svg
                                wire:loading
                                wire:target="submitReply"
                                class="h-4 w-4 animate-spin"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"
                                        stroke-width="4"></circle>
                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"
                                      stroke-linecap="round"></path>
                            </svg>

                            <span wire:loading.remove wire:target="submitReply">
                                ارسال پاسخ
                            </span>

                            <span wire:loading wire:target="submitReply">
                                در حال ارسال پاسخ...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</article>
