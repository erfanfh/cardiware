<x-front.modal
    name="ticket-access-denied"
    title="امکان ثبت تیکت وجود ندارد"
    description="دسترسی ثبت تیکت برای حساب شما فعال نیست."
    max-width="max-w-lg"
>
    <div class="space-y-5">
        <div
            class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-rose-50 text-rose-600 ring-1 ring-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/20">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                <path
                    d="M12 9v4M12 17h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </div>

        <div class="text-center">
            <div class="text-sm font-black leading-7 text-slate-900 dark:text-white">
                شما در حال حاضر اجازه ارسال تیکت ندارید
            </div>

            <p class="mx-auto mt-2 max-w-md text-xs font-medium leading-6 text-slate-500 sm:text-sm sm:leading-7 dark:text-slate-400">
                ثبت تیکت پشتیبانی فقط برای کاربران VIP یا کاربرانی که حداقل یک محصول تهیه کرده‌اند فعال است.
            </p>
        </div>

        <div
            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
            <div class="text-xs font-black text-slate-800 dark:text-slate-100">
                راه‌های فعال شدن پشتیبانی
            </div>

            <div class="mt-3 space-y-2 text-xs font-bold leading-6 text-slate-500 dark:text-slate-400">
                <div class="flex items-start gap-2">
                    <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>
                    خرید حداقل یک محصول از مودینگ وی
                </div>

                <div class="flex items-start gap-2">
                    <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>
                    فعال بودن وضعیت VIP برای حساب کاربری
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button
                type="button"
                x-on:click="$dispatch('close-modal', 'ticket-access-denied')"
                class="inline-flex h-11 w-full cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-extrabold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 sm:w-auto dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
            >
                متوجه شدم
            </button>
        </div>
    </div>
</x-front.modal>
