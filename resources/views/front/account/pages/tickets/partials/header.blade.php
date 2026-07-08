<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        <div class="flex items-center gap-2">
            <div
                class="grid h-9 w-9 place-items-center rounded-2xl bg-brand-50 text-brand-600 ring-1 ring-brand-100 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/20">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M8 10h8M8 14h5M21 12c0 4.418-4.03 8-9 8a10.4 10.4 0 0 1-4.064-.816L3 20l1.26-3.36A7.57 7.57 0 0 1 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>

            <div class="min-w-0">
                <div class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white">
                    تیکت‌های پشتیبانی
                </div>

                <div class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                    ارتباط مستقیم با تیم پشتیبانی مودینگ وی
                </div>
            </div>
        </div>
    </div>

    <button
        type="button"
        @click="openCreateTicketModal()"
        class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-600 px-4 text-sm font-extrabold text-white shadow-sm shadow-brand-500/20 transition hover:-translate-y-0.5 hover:bg-brand-700 hover:shadow-lg hover:shadow-brand-500/25 focus:outline-none focus:ring-4 focus:ring-brand-100 sm:w-auto dark:bg-brand-500 dark:hover:bg-brand-400 dark:focus:ring-brand-500/20"
    >
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
            <path
                d="M12 5v14M5 12h14"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
            />
        </svg>

        ثبت تیکت جدید
    </button>
</div>
