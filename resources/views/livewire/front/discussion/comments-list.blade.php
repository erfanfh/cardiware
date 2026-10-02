<div
    id="discussion-comments"
    class="scroll-mt-36"
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
            this.$refs.commentsListTop?.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        },
        handlePaginationClick(event) {
            const clickable = event.target.closest('button,a');

            if (! clickable) {
                return;
            }

            this.scrollToCommentsTop();
        }
    }"
    x-init="scrollToNotificationTarget()"
>
    <x-front.cart>
        <div class="scroll-mt-40" x-ref="commentsListTop"></div>

        <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-3">
                <span
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700 [&_svg]:h-[1.125rem] [&_svg]:w-[1.125rem] dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                    @includeIf('icons.message-done-01')
                </span>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-base font-extrabold dark:text-slate-100">دیدگاه‌ها</h2>

                        <span
                            class="inline-flex h-7 min-w-7 items-center justify-center rounded-full border border-slate-200 bg-slate-100 px-2 text-xs font-extrabold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            {{ $this->totalComments }}
                        </span>

                        @include('front.partials.rating-summary', [
                            'averageRating' => $averageRating,
                            'ratingCount' => $ratingCount,
                        ])
                    </div>

                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        دیدگاه کاربران درباره این محتوا را بخوانید.
                    </p>
                </div>
            </div>

            @if($isOpen)
                <div class="flex w-full items-center gap-2 self-start sm:w-auto sm:self-auto">
                    <span class="shrink-0 text-xs font-semibold text-slate-500 dark:text-slate-400">مرتب‌سازی:</span>

                    <div class="relative flex-1 sm:flex-none">
                        <select
                            wire:model.live="sort"
                            class="h-11 w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 pr-4 pl-10 text-sm font-bold text-slate-700 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100 sm:w-auto dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-brand-500/60 dark:focus:bg-slate-950 dark:focus:ring-brand-500/10"
                        >
                            <option value="new">جدیدترین</option>
                            <option value="top">محبوب‌ترین</option>
                            <option value="high">بیشترین امتیاز</option>
                        </select>

                        <span
                            class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400 dark:text-slate-500">
                            <svg viewBox="0 0 20 20" class="h-4 w-4 fill-current">
                                <path d="M5.25 7.5 10 12.25 14.75 7.5"/>
                            </svg>
                        </span>
                    </div>
                </div>
            @endif
        </div>

        @if(! $isOpen)
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
                                            نظر کاربران درباره این محتوا
                                        </div>
                                        <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                            تجربه نصب، کیفیت محتوا و مشکلات احتمالی را از دید کاربران بخوانید.
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <span
                                        class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                        <span class="ml-1.5 inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                                        {{ $this->totalComments }} دیدگاه ثبت شده
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

                                    <span class="max-sm:hidden">مشاهده {{ $this->totalComments }} دیدگاه</span>
                                    <span class="sm:hidden">مشاهده دیدگاه‌ها</span>
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
                                            هنوز دیدگاهی ثبت نشده است
                                        </div>
                                        <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                            اولین نفری باشید که تجربه‌تان را درباره این محتوا ثبت می‌کند.
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <span
                                        class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                        منتظر اولین دیدگاه
                                    </span>

                                    <span
                                        class="inline-flex items-center rounded-2xl border border-brand-100 bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                                        ثبت دیدگاه شما مهم است
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

                                    بررسی دیدگاه‌ها
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="relative mt-6">
                <div
                    wire:loading.flex
                    wire:target="gotoPage,previousPage,nextPage,sort"
                    class="absolute inset-0 z-20 items-start justify-center rounded-[1.5rem] bg-white/70 px-3 pt-10 backdrop-blur-[1px] dark:bg-slate-950/70"
                >
                    <div
                        class="inline-flex max-w-full items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-extrabold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:shadow-black/20">
                        <svg class="h-4 w-4 animate-spin shrink-0" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"
                                    stroke-width="4"></circle>
                            <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"
                                  stroke-linecap="round"></path>
                        </svg>
                        <span class="truncate">در حال بارگذاری دیدگاه‌ها...</span>
                    </div>
                </div>

                <div
                    class="space-y-4 sm:space-y-5"
                    wire:loading.class="pointer-events-none opacity-60"
                    wire:target="gotoPage,previousPage,nextPage,sort"
                >
                    @if($this->comments->count() === 0)
                        <div
                            class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 p-6 text-center sm:p-8 dark:border-slate-700 dark:bg-slate-950">
                            <div
                                class="mx-auto grid h-14 w-14 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-400 [&_svg]:h-6 [&_svg]:w-6 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-500">
                                @include('icons.comment-add-01')
                            </div>

                            <h3 class="mt-4 text-sm font-extrabold text-slate-800 dark:text-slate-200">هنوز دیدگاهی ثبت
                                نشده است</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                اولین نفری باشید که دیدگاهش را درباره این محتوا ثبت می‌کند.
                            </p>
                        </div>
                    @endif

                    @foreach($this->comments as $comment)
                        <livewire:front.discussion.comment-item
                            :comment-id="$comment->id"
                            :commentable-type="$this->commentableType"
                            :commentable-id="$this->commentableId"
                            :level="0"
                            :notification-target-path="$notificationTargetPath"
                            :key="'discussion-root-comment-'.$comment->id.'-'.$this->comments->currentPage().'-'.$sort"
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
    </x-front.cart>
</div>
