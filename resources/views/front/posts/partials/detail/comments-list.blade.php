<section
    x-data="commentsWidget({
        listEndpoint: '{{ route('posts.comments.index', $post) }}',
        initialTotalComments: {{ $approvedCommentsCount ?? 0 }}
    })"
    x-init="init()"
    class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm"
>
    <div class="p-5 sm:p-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <span
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path>
                    </svg>
                </span>

                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-extrabold text-slate-900">دیدگاه‌ها</h2>

                        <span
                            class="inline-flex h-7 min-w-7 items-center justify-center rounded-full border border-slate-200 bg-slate-100 px-2 text-xs font-extrabold text-slate-700"
                            x-text="displayTotalCount()"
                        ></span>
                    </div>

                    <p class="mt-1 text-sm text-slate-500">
                        دیدگاه کاربران درباره این محتوا را بخوانید.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start lg:self-auto" x-show="isOpen">
                <span class="text-xs font-semibold text-slate-500">مرتب‌سازی:</span>

                <div class="relative">
                    <select
                        x-model="sort"
                        @change="changeSort($event.target.value)"
                        :disabled="loading"
                        class="h-11 appearance-none rounded-2xl border border-slate-200 bg-slate-50 pr-4 pl-10 text-sm font-bold text-slate-700 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-4 focus:ring-brand-100 disabled:opacity-60"
                    >
                        <option value="new">جدیدترین</option>
                        <option value="top">محبوب‌ترین</option>
                        <option value="high">بیشترین امتیاز</option>
                    </select>

                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <svg viewBox="0 0 20 20" class="h-4 w-4 fill-current">
                            <path d="M5.25 7.5 10 12.25 14.75 7.5"/>
                        </svg>
                    </span>
                </div>
            </div>
        </div>

        {{-- Closed State --}}
        <div x-show="!isOpen" x-cloak class="mt-6">
            <button
                type="button"
                @click="openComments()"
                :disabled="loading"
                class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-5 text-sm font-extrabold text-slate-800 transition hover:border-slate-300 hover:bg-white disabled:cursor-not-allowed disabled:opacity-60"
            >
                <svg
                    x-show="loading"
                    x-cloak
                    class="h-4 w-4 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"
                            stroke-width="4"></circle>
                    <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"
                          stroke-linecap="round"></path>
                </svg>

                <span x-text="loading ? 'در حال بارگذاری دیدگاه‌ها...' : openButtonLabel()"></span>
            </button>

            <div
                x-show="!isOpen && errorMessage"
                x-cloak
                x-transition.opacity.duration.250ms
                class="mt-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700"
                x-text="errorMessage"
            ></div>
        </div>

        {{-- Open State --}}
        <div x-show="isOpen" x-cloak x-transition.opacity.duration.250ms class="mt-6 space-y-4 sm:space-y-5">
            {{-- Error --}}
            <div
                x-show="errorMessage"
                x-cloak
                class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700"
                x-text="errorMessage"
            ></div>

            {{-- Skeleton --}}
            <template x-if="loading && !comments.length">
                <div class="space-y-4">
                    <template x-for="n in 3" :key="'skeleton-'+n">
                        <div class="rounded-[1.5rem] border border-slate-200 bg-white p-4 sm:p-5">
                            <div class="animate-pulse">
                                <div class="flex gap-3 sm:gap-4">
                                    <div class="h-11 w-11 rounded-2xl bg-slate-200"></div>

                                    <div class="flex-1 space-y-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="space-y-2">
                                                <div class="h-4 w-32 rounded bg-slate-200"></div>
                                                <div class="h-3 w-24 rounded bg-slate-100"></div>
                                            </div>

                                            <div class="h-8 w-24 rounded-2xl bg-slate-100"></div>
                                        </div>

                                        <div class="space-y-2">
                                            <div class="h-3 rounded bg-slate-100"></div>
                                            <div class="h-3 rounded bg-slate-100"></div>
                                            <div class="h-3 w-2/3 rounded bg-slate-100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Empty State --}}
            <template x-if="!loading && comments.length === 0">
                <div class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 p-6 text-center sm:p-8">
                    <div
                        class="mx-auto grid h-14 w-14 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-400">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
                        </svg>
                    </div>

                    <h3 class="mt-4 text-sm font-extrabold text-slate-800">هنوز دیدگاهی ثبت نشده است</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        اولین نفری باشید که دیدگاهش را درباره این محتوا ثبت می‌کند.
                    </p>
                </div>
            </template>

            {{-- List --}}
            <template x-for="c in comments" :key="c.id">
                <article
                    class="group rounded-[1.5rem] border border-slate-200 bg-white p-4 transition hover:border-slate-300 sm:p-5">
                    <div class="flex gap-3 sm:gap-4">
                        <div
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-50 to-slate-100 text-sm font-black text-slate-700"
                            x-text="avatarText(c.name)"
                        ></div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <div class="truncate text-sm font-black text-slate-900" x-text="c.name"></div>
                                        <span class="text-xs text-slate-400">•</span>
                                        <span class="text-xs text-slate-500" x-text="c.time"></span>
                                    </div>

                                    <div class="mt-2 flex items-center gap-0.5">
                                        <template x-for="n in 5" :key="'cstar'+c.id+'-'+n">
                                            <svg
                                                viewBox="0 0 20 20"
                                                class="h-4 w-4"
                                                :class="c.rating >= n ? 'text-amber-400' : 'text-slate-200'"
                                            >
                                                <path
                                                    fill="currentColor"
                                                    d="M10 15.27l-5.18 2.73 0.99-5.82L1.62 7.9l5.84-.85L10 1.75l2.54 5.3 5.84.85-4.19 4.28.99 5.82z"
                                                />
                                            </svg>
                                        </template>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 self-start">
                                    <button
                                        @click="toggleLike(c)"
                                        type="button"
                                        class="inline-flex h-10 items-center gap-2 rounded-2xl border px-3 text-sm font-extrabold transition"
                                        :class="c.liked
                                            ? 'border-brand-200 bg-brand-50 text-brand-700'
                                            : 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-white hover:border-slate-300'"
                                    >
                                        <span class="text-base leading-none">♥</span>
                                        <span x-text="c.likes"></span>
                                    </button>

                                    <button
                                        @click="startReply(c)"
                                        type="button"
                                        class="inline-flex h-10 items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-sm font-extrabold text-slate-700 transition hover:border-slate-300 hover:bg-white"
                                    >
                                        <span>پاسخ</span>
                                        <span class="text-slate-400">↩</span>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3 text-[0.95rem] leading-8 text-slate-700" x-text="c.text"></div>

                            {{-- Reply Form: فعلاً نگهش می‌داریم برای مرحله بعد --}}
                            <div
                                x-show="replyTo === c.id"
                                x-transition
                                class="mt-4 rounded-[1.25rem] border border-slate-200 bg-slate-50/80 p-4"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm font-black text-slate-800">
                                        پاسخ به <span class="text-brand-700" x-text="c.name"></span>
                                    </div>

                                    <button
                                        @click="replyTo = null"
                                        type="button"
                                        class="grid h-9 w-9 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-700"
                                    >
                                        ×
                                    </button>
                                </div>

                                <textarea
                                    rows="3"
                                    class="mt-3 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100"
                                    placeholder="پاسخ شما…"
                                ></textarea>

                                <div class="mt-3 flex items-center justify-end gap-2">
                                    <button
                                        @click="replyTo = null"
                                        type="button"
                                        class="h-10 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50"
                                    >
                                        لغو
                                    </button>

                                    <button
                                        type="button"
                                        class="h-10 rounded-2xl bg-brand-500 px-4 text-sm font-extrabold text-white transition hover:bg-brand-600 active:bg-brand-700"
                                    >
                                        ارسال پاسخ
                                    </button>
                                </div>
                            </div>

                            <div
                                class="mt-4 space-y-3 border-r-2 border-slate-100 pr-3 sm:pr-4"
                                x-show="c.replies?.length"
                            >
                                <template x-for="r in c.replies" :key="r.id">
                                    <div class="rounded-[1.25rem] border border-slate-200 bg-slate-50 p-3.5">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="grid h-9 w-9 shrink-0 place-items-center rounded-2xl border border-slate-200 bg-white text-sm font-black text-slate-700"
                                                x-text="avatarText(r.name)"
                                            ></div>

                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                                    <div class="truncate text-sm font-black text-slate-900"
                                                         x-text="r.name"></div>
                                                    <span class="text-xs text-slate-400">•</span>
                                                    <div class="text-xs text-slate-500" x-text="r.time"></div>
                                                    <span
                                                        class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-2 py-1 text-[11px] font-extrabold text-slate-600">
                                                        پاسخ
                                                    </span>
                                                </div>

                                                <div class="mt-2 text-sm leading-7 text-slate-700"
                                                     x-text="r.text"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </article>
            </template>

            {{-- Pagination --}}
            <div
                x-show="showPagination()"
                class="flex flex-wrap items-center justify-center gap-2 pt-2"
            >
                <button
                    type="button"
                    @click="goToPage(meta.current_page - 1)"
                    :disabled="loading || meta.current_page <= 1"
                    class="inline-flex h-10 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    قبلی
                </button>

                <template x-for="pageNumber in paginationItems()" :key="'page-'+pageNumber">
                    <button
                        type="button"
                        @click="goToPage(pageNumber)"
                        :disabled="loading"
                        class="inline-flex h-10 min-w-10 items-center justify-center rounded-2xl border px-3 text-sm font-extrabold transition"
                        :class="pageNumber === meta.current_page
                            ? 'border-brand-200 bg-brand-50 text-brand-700'
                            : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                        x-text="pageNumber"
                    ></button>
                </template>

                <button
                    type="button"
                    @click="goToPage(meta.current_page + 1)"
                    :disabled="loading || meta.current_page >= meta.last_page"
                    class="inline-flex h-10 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    بعدی
                </button>
            </div>
        </div>
    </div>

    <div class="border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-xs leading-6 text-slate-500">
                برای گزارش مشکل لینک‌ها، از بخش دانلود استفاده کنید.
            </div>

            <a
                href="#"
                class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-700 transition hover:text-brand-800"
            >
                قوانین دیدگاه‌ها
                <span class="font-black">←</span>
            </a>
        </div>
    </div>
</section>
