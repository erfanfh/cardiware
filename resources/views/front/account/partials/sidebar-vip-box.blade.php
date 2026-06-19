<x-front.cart
    x-data="{
        hasVip: false,
        remainingDays: 23,
        progressPercent: 72
    }"
    class="overflow-hidden border-slate-200 bg-white/90 dark:border-slate-800 dark:bg-slate-900/90"
>
    <!-- Active VIP -->
    <template x-if="hasVip">
        <div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300 [&_svg]:h-5 [&_svg]:w-5">
                            @include('icons.crown-03')
                        </span>

                        <div class="text-base font-extrabold text-slate-900 dark:text-white">
                            اشتراک VIP
                        </div>
                    </div>

                    <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                        دسترسی شما به مادها و امکانات ویژه فعال است
                    </div>
                </div>
            </div>

            <div
                class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-500 dark:text-slate-400">اعتبار باقی‌مانده</span>
                    <span class="font-extrabold text-slate-800 dark:text-white" x-text="remainingDays + ' روز'"></span>
                </div>

                <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                    <div
                        class="h-full rounded-full bg-emerald-500 dark:bg-emerald-400"
                        :style="`width: ${progressPercent}%`"
                    ></div>
                </div>

                <div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                    <span>تا پایان دسترسی VIP</span>
                    <span class="font-bold text-emerald-700 dark:text-emerald-300"
                          x-text="progressPercent + '%'"></span>
                </div>
            </div>

            <button
                type="button"
                @click="setTab('orders')"
                class="mt-5 inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-3 text-[0.8rem] font-extrabold text-white transition hover:opacity-95 dark:bg-white dark:text-slate-950 [&_svg]:h-4 [&_svg]:w-4"
            >
                تمدید اشتراک VIP
                @include('icons.arrow-left-02')
            </button>
        </div>
    </template>

    <!-- Inactive VIP -->
    <template x-if="!hasVip">
        <div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300 [&_svg]:h-5 [&_svg]:w-5">
                            @include('icons.crown-03')
                        </span>

                        <div class="text-base font-extrabold text-slate-900 dark:text-white">
                            اشتراک VIP ندارید
                        </div>
                    </div>

                    <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                        مادهای ویژه و امکانات حرفه‌ای برای شما قفل هستند
                    </div>
                </div>
            </div>

            <div
                class="mt-5 rounded-2xl border border-amber-100 bg-amber-50/70 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
                <div class="text-sm font-extrabold text-slate-900 dark:text-white">
                    یک قدم عقب‌تر از کاربران VIP هستید
                </div>

                <p class="mt-2 text-xs leading-6 text-slate-600 dark:text-slate-400">
                    با فعال‌سازی VIP، به مادهای ویژه، دانلودهای اختصاصی و امکاناتی دسترسی پیدا می‌کنید که برای
                    کاربران عادی در دسترس نیست.
                </p>

                <div class="mt-4 grid gap-2 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <div class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500 dark:bg-amber-400"></span>
                        دسترسی به محتوای ویژه
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500 dark:bg-amber-400"></span>
                        تجربه سریع‌تر و حرفه‌ای‌تر
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500 dark:bg-amber-400"></span>
                        مناسب برای کاربران جدی مودینگ‌وی
                    </div>
                </div>
            </div>

            <button
                type="button"
                x-data
                @click="$dispatch('open-modal', 'vip-coming-soon-modal')"
                class="mt-5 inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-amber-500 px-4 py-3 text-sm font-extrabold text-white transition hover:bg-amber-600 dark:bg-amber-500 dark:hover:bg-amber-400 [&_svg]:h-4 [&_svg]:w-4"
            >
                فعال‌سازی اشتراک VIP
                @include('icons.arrow-left-02')
            </button>
        </div>
    </template>
</x-front.cart>

<x-front.modal
    name="vip-coming-soon-modal"
    title="اشتراک VIP در راه است"
    description="یک قابلیت ویژه برای کاربران حرفه‌ای مودینگ‌وی به زودی فعال می‌شود."
    maxWidth="max-w-lg"
>
    <div class="rounded-3xl border border-amber-100 bg-amber-50 p-5 dark:border-amber-500/20 dark:bg-amber-500/10">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
            <div
                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-3xl border border-amber-200 bg-white text-amber-600 shadow-sm dark:border-amber-500/20 dark:bg-slate-900 dark:text-amber-300">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                    <path d="M12 3l2.7 5.47 6.03.88-4.36 4.25 1.03 6-5.4-2.84-5.4 2.84 1.03-6-4.36-4.25 6.03-.88L12 3Z"
                          stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                </svg>
            </div>

            <div>
                <h3 class="text-sm font-black text-slate-900 dark:text-white">
                    دسترسی ویژه به زودی فعال می‌شود
                </h3>

                <p class="mt-2 text-[0.8rem] font-bold leading-6 text-slate-600 dark:text-slate-400">
                    با اشتراک VIP، دسترسی سریع‌تر به امکانات ویژه، پیشنهادهای اختصاصی و تجربه حرفه‌ای‌تر از مودینگ‌وی
                    برای کاربران فعال فراهم می‌شود.
                </p>
            </div>
        </div>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-3">
        @foreach([
            ['title' => 'مزایای ویژه', 'desc' => 'امکانات اختصاصی'],
            ['title' => 'اولویت دسترسی', 'desc' => 'زودتر از بقیه'],
            ['title' => 'پیشنهاد خاص', 'desc' => 'مخصوص VIPها'],
        ] as $item)
            <div
                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-center dark:border-slate-700 dark:bg-slate-800/70">
                <div class="text-sm font-black text-slate-900 dark:text-white">{{ $item['title'] }}</div>
                <div class="mt-1 text-xs font-bold leading-6 text-slate-500 dark:text-slate-400">
                    {{ $item['desc'] }}
                </div>
            </div>
        @endforeach
    </div>

    <x-slot name="footer">
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
            <button
                type="button"
                x-data
                @click="$dispatch('close-modal', 'vip-coming-soon-modal')"
                class="inline-flex h-11 cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
            >
                بعداً بررسی می‌کنم
            </button>

            <button
                type="button"
                x-data
                @click="$dispatch('close-modal', 'vip-coming-soon-modal')"
                class="inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-amber-500 px-5 text-sm font-extrabold text-white shadow-soft transition hover:bg-amber-600 dark:bg-amber-500 dark:hover:bg-amber-400"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path d="M12 3l2.7 5.47 6.03.88-4.36 4.25 1.03 6-5.4-2.84-5.4 2.84 1.03-6-4.36-4.25 6.03-.88L12 3Z"
                          stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                </svg>

                منتظر VIP می‌مانم
            </button>
        </div>
    </x-slot>
</x-front.modal>
