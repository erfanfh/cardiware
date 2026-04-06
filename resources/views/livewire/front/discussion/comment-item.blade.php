<article
    @class([
        'group rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300 sm:p-5',
        'mt-3' => $level > 0,
    ])
>
    <div class="flex gap-3 sm:gap-4">
        <div
            class="grid h-11 w-11 shrink-0 place-items-center overflow-hidden rounded-2xl border border-slate-200 text-sm font-black {{ $this->avatarColor() }}">
            @if($this->comment->user->avatar)
                <div class="bg-white">
                    <img src="{{ $this->comment->user->avatar->url }}" class="w-full h-full" alt="IMG">
                </div>
            @endif
            <span>{{ $this->avatarText() }}</span>
        </div>

        <div class="min-w-0 flex-1">
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
                        wire:click="startReply"
                        type="button"
                        class="inline-flex h-10 cursor-pointer items-center gap-1.5 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-xs font-extrabold text-slate-700 transition hover:border-slate-300 hover:bg-white"
                    >
                        <span>پاسخ</span>
                        <span class="text-slate-400">
                            @include('icons.arrow-left-double')
                        </span>
                    </button>

                    @if($this->comment->approved_replies_count > 0)
                        <button
                            wire:click="toggleReplies"
                            type="button"
                            class="inline-flex h-10 cursor-pointer items-center gap-1.5 rounded-2xl border border-slate-200 bg-white px-3 text-xs font-extrabold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                        >
                            <span>
                                {{ $repliesOpen ? 'بستن پاسخ‌ها' : 'نمایش پاسخ‌ها (' . $this->comment->approved_replies_count . ')' }}
                            </span>
                        </button>
                    @endif
                </div>
            </div>

            <div class="tinymce-content mt-3 text-sm leading-8 text-slate-700">
                {!! $this->comment->safe_content !!}
            </div>

            @if($replyFormOpen)
                <div class="mt-4 rounded-[1.25rem] border border-slate-200 bg-slate-50/80 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm font-black text-slate-800">
                            پاسخ به <span class="text-brand-700">{{ $this->comment->display_name }}</span>
                        </div>

                        <button
                            wire:click="closeReply"
                            type="button"
                            class="grid h-9 w-9 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-700"
                        >
                            ×
                        </button>
                    </div>

                    @guest
                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <input
                                    wire:model.live="replyAuthorName"
                                    type="text"
                                    placeholder="نام شما"
                                    class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100"
                                >
                                @error('replyAuthorName')
                                <div class="mt-2 text-xs font-bold text-red-600">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <input
                                    wire:model.live="replyAuthorEmail"
                                    type="email"
                                    placeholder="ایمیل شما"
                                    class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100"
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
                        wire:model.live="replyContent"
                        rows="3"
                        class="mt-3 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100"
                        placeholder="پاسخ شما…"
                    ></textarea>

                    @error('replyContent')
                    <div class="mt-2 text-xs font-bold text-red-600">{{ $message }}</div>
                    @enderror

                    <div class="mt-3 flex items-center justify-end gap-2">
                        <button
                            wire:click="closeReply"
                            type="button"
                            class="h-10 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50"
                        >
                            لغو
                        </button>

                        <button
                            wire:click="submitReply"
                            wire:loading.attr="disabled"
                            wire:target="submitReply"
                            type="button"
                            class="h-10 rounded-2xl bg-brand-500 px-4 text-sm font-extrabold text-white transition hover:bg-brand-600 active:bg-brand-700 disabled:opacity-70"
                        >
                            <span wire:loading.remove wire:target="submitReply">ارسال پاسخ</span>
                            <span wire:loading wire:target="submitReply">در حال ارسال...</span>
                        </button>
                    </div>
                </div>
            @endif

            @if($repliesOpen && $this->replies->isNotEmpty())
                <div class="mt-4 border-r-2 border-slate-100 pr-3 sm:pr-4">
                    @foreach($this->replies as $reply)
                        <livewire:front.discussion.comment-item
                            :comment-id="$reply->id"
                            :commentable="$commentable"
                            :level="$level + 1"
                            :key="'discussion-reply-'.$reply->id.'-parent-'.$this->comment->id"
                        />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</article>
