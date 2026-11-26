<div
    x-data="{
        notificationTargetId: @js($notificationTargetCommentId),
        scrollToNotificationTarget() {
            if (!this.notificationTargetId) return;
            this.$nextTick(() => {
                const target = document.getElementById('discussion-comment-' + this.notificationTargetId);
                target?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        },
        scrollToCommentsTop() {
            this.$refs.productCommentsTop?.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        },
        handlePaginationClick(event) {
            const clickable = event.target.closest('button,a');

            if (! clickable) return;

            this.scrollToCommentsTop();
        }
    }"
    x-init="scrollToNotificationTarget()"
    x-on:product-comments-refresh-requested.window="
        $nextTick(() => {
            scrollToCommentsTop();
        })
    "
>
    <div class="scroll-mt-40" x-ref="productCommentsTop"></div>

    @if($isOpen)
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
            <span
                class="inline-flex h-8 min-w-8 items-center justify-center rounded-full border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800 px-2 text-xs font-black text-slate-700 dark:text-slate-200">
                {{ $this->totalComments }}
            </span>

                <span class="text-sm font-extrabold text-slate-800 dark:text-slate-100">
                {{ $this->totalComments > 0 ? 'Comment submitted' : 'No comments yet' }}
            </span>
            </div>

            <div class="flex items-center gap-2">
            <span class="shrink-0 text-xs font-bold text-slate-500 dark:text-slate-400">
                Sort:
            </span>

                <div class="relative min-w-40 flex-1 sm:flex-none">
                    <select
                        wire:model.live="sort"
                        class="h-11 w-full appearance-none rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 pr-4 pl-10 text-sm font-bold text-slate-700 dark:text-slate-200 outline-none transition focus:border-brand-300 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-brand-100"
                    >
                        <option value="new">Newest</option>
                        <option value="top">Most popular</option>
                        <option value="high">Highest rated</option>
                    </select>

                    <span
                        class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400 dark:text-slate-500">
                    <svg viewBox="0 0 20 20" class="h-4 w-4 fill-current">
                        <path d="M5.25 7.5 10 12.25 14.75 7.5"/>
                    </svg>
                </span>
                </div>
            </div>
        </div>

        @if($this->totalComments === 0 && $this->comments->count() === 0)
            <div
                class="rounded-3xl border border-dashed border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-5 sm:p-6">
                <div class="flex items-start gap-3">
                    <div
                        class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl border border-brand-100 dark:border-brand-900/40 bg-brand-50 dark:bg-brand-950/30 text-brand-700 dark:text-brand-400">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M12 5v10M7 10h10M21 11.5c0 4.14-4.03 7.5-9 7.5a10.4 10.4 0 0 1-3.22-.5L3 20l1.7-4.05A7 7 0 0 1 3 11.5C3 7.36 7.03 4 12 4s9 3.36 9 7.5Z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>

                    <div>
                        <div class="text-sm font-black text-slate-900 dark:text-slate-100">
                            No comments have been posted for this product yet
                        </div>

                        <p class="mt-1 text-sm leading-7 text-slate-500 dark:text-slate-400">
                            Be the first to share your experience using or buying this product.
                        </p>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300">
                            Be the first to comment
                        </span>

                            <span
                                class="inline-flex items-center rounded-2xl border border-brand-100 dark:border-brand-900/40 bg-brand-50 dark:bg-brand-950/30 px-3 py-1.5 text-xs font-bold text-brand-700 dark:text-brand-400">
                            Your experience matters
                        </span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="relative">
                <div
                    wire:loading.flex
                    wire:target="gotoPage,previousPage,nextPage,sort"
                    class="absolute inset-0 z-20 items-start justify-center rounded-3xl bg-white/70 dark:bg-slate-900/70 px-3 pt-10 backdrop-blur-[1px]"
                >
                    <div
                        class="inline-flex max-w-full items-center gap-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2 text-xs font-black text-slate-700 dark:text-slate-200 shadow-sm">
                        <svg class="h-4 w-4 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none">
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

                        <span class="truncate">
                        Loading comments...
                    </span>
                    </div>
                </div>

                <div
                    class="space-y-4"
                    wire:loading.class="pointer-events-none opacity-60"
                    wire:target="gotoPage,previousPage,nextPage,sort"
                >
                    @foreach($this->comments as $comment)
                        <livewire:front.discussion.comment-item
                            :comment-id="$comment->id"
                            :commentable-type="$this->commentableType"
                            :commentable-id="$this->commentableId"
                            :level="0"
                            :show-replies="true"
                            :allow-reply-submission="false"
                            :notification-target-path="$notificationTargetPath"
                            :key="'product-root-comment-'.$comment->id.'-'.$this->comments->currentPage().'-'.$sort.'-show-1-submit-0'"
                        />
                    @endforeach

                    @if($this->comments->hasPages())
                        <div
                            class="pt-2"
                            x-on:click.capture="handlePaginationClick($event)"
                        >
                            {{ $this->comments->onEachSide(1)->links(data: ['scrollTo' => false]) }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @else
        <div class="mt-6">
            @if($this->totalComments > 0)
                <div
                    class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-gradient-to-b from-slate-50 to-white p-5 sm:p-6 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:gap-3">
                                <div class="flex shrink-0 -space-x-2 rtl:space-x-reverse">
                                        <span
                                            class="grid h-10 w-10 place-items-center rounded-2xl border border-white bg-slate-200 text-xs font-black text-slate-700 dark:border-slate-900 dark:bg-slate-700 dark:text-slate-200">A</span>
                                    <span
                                        class="grid h-10 w-10 place-items-center rounded-2xl border border-white bg-slate-100 text-xs font-black text-slate-700 dark:border-slate-900 dark:bg-slate-800 dark:text-slate-200">M</span>
                                    <span
                                        class="grid h-10 w-10 place-items-center rounded-2xl border border-white bg-slate-50 text-xs font-black text-slate-700 dark:border-slate-900 dark:bg-slate-900 dark:text-slate-200">N</span>
                                </div>

                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                                        What users think about this content
                                    </div>
                                    <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                        Read user experiences with installation, content quality and potential issues.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <span
                                        class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                        <span class="ml-1.5 inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                                        {{ $this->totalComments }} Comment submitted
                                    </span>
                            </div>
                        </div>

                        <div class="shrink-0 max-sm:w-full">
                            <button
                                type="button"
                                wire:click="openComments"
                                wire:loading.attr="disabled"
                                class="inline-flex h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 text-sm font-extrabold text-white transition hover:-translate-y-[1px] hover:opacity-95 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto dark:bg-brand-500 dark:hover:bg-brand-400"
                            >
                                <svg
                                    wire:loading
                                    wire:target="openComments"
                                    class="h-4 w-4 animate-spin"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"
                                            stroke-width="4"></circle>
                                    <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"
                                          stroke-linecap="round"></path>
                                </svg>

                                <span class="max-sm:hidden">View {{ $this->totalComments }} Comment</span>
                                <span class="sm:hidden">View comments</span>
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div
                    class="overflow-hidden rounded-[1.5rem] border border-dashed border-slate-200 bg-gradient-to-b from-slate-50 to-white p-5 sm:p-6 dark:border-slate-700 dark:from-slate-900 dark:to-slate-950">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex items-start gap-3">
                                <div
                                    class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700 [&_svg]:h-6 [&_svg]:w-6 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                    @include('icons.add-01')
                                </div>

                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                                        No comments yet
                                    </div>
                                    <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                        Be the first to share your experience with this content.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <span
                                        class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                        Be the first to comment
                                    </span>

                                <span
                                    class="inline-flex items-center rounded-2xl border border-brand-100 bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                        Your feedback matters
                                    </span>
                            </div>
                        </div>

                        <div class="shrink-0 max-sm:w-full">
                            <button
                                type="button"
                                wire:click="openComments"
                                wire:loading.attr="disabled"
                                class="inline-flex h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white transition hover:bg-brand-600 hover:-translate-y-[1px] disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto dark:hover:bg-brand-400"
                            >
                                <svg
                                    wire:loading
                                    wire:target="openComments"
                                    class="h-4 w-4 animate-spin"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"
                                            stroke-width="4"></circle>
                                    <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"
                                          stroke-linecap="round"></path>
                                </svg>

                                Review comments
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
