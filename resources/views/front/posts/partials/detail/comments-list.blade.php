<x-front.cart
    x-data="commentsWidget({
        listEndpoint: '{{ route('posts.comments.index', $post) }}',
        initialTotalComments: {{ $post->approved_comments_count ?? 0 }}
    })"
    x-init="init()">

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-5">
        <div class="flex items-center gap-3">
        <span
            class="inline-flex h-9 w-9 [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem] rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
            @includeIf('icons.message-done-01')
        </span>

            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-extrabold">دیدگاه‌ها</h2>

                    <span
                        class="inline-flex h-7 min-w-7 items-center justify-center rounded-full border border-slate-200 bg-slate-100 px-2 text-xs font-extrabold text-slate-700"
                        x-text="displayTotalCount()"
                    ></span>
                </div>

                <p class="mt-1 text-xs text-slate-500">
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
        {{-- Has Comments --}}
        <template x-if="displayTotalCount() > 0">
            <div
                class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-gradient-to-b from-slate-50 to-white p-5 sm:p-6">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <div class="flex items-center gap-3">
                            <div class="flex -space-x-2 rtl:space-x-reverse">
                                <span
                                    class="grid h-10 w-10 place-items-center rounded-2xl border border-white bg-slate-200 text-xs font-black text-slate-700 shadow-sm">A</span>
                                <span
                                    class="grid h-10 w-10 place-items-center rounded-2xl border border-white bg-slate-100 text-xs font-black text-slate-700 shadow-sm">M</span>
                                <span
                                    class="grid h-10 w-10 place-items-center rounded-2xl border border-white bg-slate-50 text-xs font-black text-slate-700 shadow-sm">N</span>
                            </div>

                            <div class="min-w-0">
                                <div class="text-sm font-extrabold text-slate-900">
                                    نظر کاربران درباره این محتوا
                                </div>
                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    تجربه نصب، کیفیت محتوا و مشکلات احتمالی را از دید کاربران بخوانید.
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600"
                            >
                                <span class="ml-1.5 inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span x-text="`${displayTotalCount()} دیدگاه ثبت شده`"></span>
                            </span>
                        </div>
                    </div>

                    <div class="shrink-0">
                        <button
                            type="button"
                            @click="openComments()"
                            :disabled="loading"
                            class="cursor-pointer inline-flex h-12 items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 text-sm font-extrabold text-white shadow-sm transition hover:-translate-y-[1px] hover:opacity-95 disabled:cursor-not-allowed disabled:opacity-60"
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

                            <span x-text="loading ? 'در حال بارگذاری...' : openButtonLabel()"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>

        {{-- Empty Comments --}}
        <template x-if="displayTotalCount() === 0">
            <div
                class="overflow-hidden rounded-[1.5rem] border border-dashed border-slate-200 bg-gradient-to-b from-slate-50 to-white p-5 sm:p-6">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <div class="flex items-start gap-3">
                            <div
                                class="grid h-12 w-12 [&_svg]:w-6 [&_svg]:h-6 shrink-0 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-700">
                                @include('icons.add-01')
                            </div>

                            <div class="min-w-0">
                                <div class="text-sm font-extrabold text-slate-900">
                                    هنوز دیدگاهی ثبت نشده است
                                </div>
                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    اولین نفری باشید که تجربه‌تان را درباره این محتوا ثبت می‌کند.
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600"
                        >
                            منتظر اولین دیدگاه
                        </span>

                            <span
                                class="inline-flex items-center rounded-2xl border border-brand-100 bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700"
                            >
                            ثبت دیدگاه شما مهم است
                        </span>
                        </div>
                    </div>

                    <div class="shrink-0">
                        <button
                            type="button"
                            @click="openComments()"
                            :disabled="loading"
                            class="cursor-pointer inline-flex h-12 items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-sm transition hover:bg-brand-600 hover:-translate-y-[1px] disabled:cursor-not-allowed disabled:opacity-60"
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

                            <span x-text="loading ? 'در حال بررسی...' : 'بررسی دیدگاه‌ها'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>

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
            <div
                class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 p-6 text-center sm:p-8">
                <div
                    class="mx-auto grid h-14 w-14 [&_svg]:w-6 [&_svg]:h-6 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-400">
                    @include('icons.comment-add-01')
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
                class="group rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300 sm:p-5">
                <div class="flex gap-3 sm:gap-4">
                    <div
                        class="grid h-11 w-11 shrink-0 place-items-center overflow-hidden rounded-2xl border border-slate-200 text-sm font-black"
                        :class="c.avatar ? 'bg-white' : avatarColor(c.name)"
                    >
                        <!-- اگر تصویر داشت -->
                        <template x-if="c.avatar">
                            <img
                                :src="c.avatar"
                                alt=""
                                class="h-full w-full object-cover"
                            >
                        </template>

                        <!-- اگر تصویر نداشت -->
                        <template x-if="!c.avatar">
                            <span x-text="avatarText(c.name)"></span>
                        </template>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <div class="truncate text-sm font-black text-slate-900"
                                         x-text="c.name"></div>
                                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
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
                                    @click="startReply(c)"
                                    type="button"
                                    class="inline-flex cursor-pointer h-10 items-center gap-1.5 rounded-2xl border border-slate-200 bg-slate-50 px-3 text-xs font-extrabold text-slate-700 transition hover:border-slate-300 hover:bg-white"
                                >
                                    <span>پاسخ</span>
                                    <span class="text-slate-400">
                                        @include('icons.arrow-left-double')
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div class="mt-3 text-sm leading-8 text-slate-700" x-html="c.text"></div>

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
                                                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
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
</x-front.cart>
