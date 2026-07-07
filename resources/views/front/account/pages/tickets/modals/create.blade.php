<x-front.modal
    name="create-ticket"
    title="ثبت تیکت جدید"
    description="درخواست خود را با جزئیات کافی برای تیم پشتیبانی ارسال کنید."
    max-width="max-w-2xl"
>
    <form
        method="POST"
        action="{{ route('account.tickets.store') }}"
        class="space-y-5"
    >
        @csrf

        <div
            class="rounded-2xl border border-brand-100 bg-brand-50/70 p-4 text-xs font-bold leading-6 text-brand-800 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-200">
            برای دریافت پاسخ بهتر، لطفاً نام محصول، شماره سفارش یا خطایی که مشاهده می‌کنید را داخل متن پیام وارد کنید.
        </div>

        <div>
            <label for="ticket-title" class="mb-2 block text-xs font-extrabold text-slate-700 dark:text-slate-200">
                عنوان تیکت
            </label>

            <input
                id="ticket-title"
                name="title"
                type="text"
                value="{{ old('title') }}"
                class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-brand-400 dark:focus:ring-brand-500/20"
            >

            @error('title', 'createTicket')
            <div class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-400">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div>
            <label for="ticket-message" class="mb-2 block text-xs font-extrabold text-slate-700 dark:text-slate-200">
                متن پیام
            </label>

            <textarea
                id="ticket-message"
                name="message"
                rows="5"
                placeholder="لطفاً مشکل یا درخواست خود را کامل توضیح دهید..."
                class="w-full resize-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold leading-7 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-brand-400 dark:focus:ring-brand-500/20"
            >{{ old('message') }}</textarea>

            @error('message', 'createTicket')
            <div class="mt-2 text-xs font-bold text-rose-600 dark:text-rose-400">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div
            class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-end dark:border-slate-800">
            <button
                type="button"
                x-on:click="$dispatch('close-modal', 'create-ticket')"
                class="inline-flex h-11 cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-extrabold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
            >
                انصراف
            </button>

            <button
                type="submit"
                class="inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-600 px-5 text-sm font-extrabold text-white shadow-sm shadow-brand-500/20 transition hover:bg-brand-700 hover:shadow-lg hover:shadow-brand-500/25 focus:outline-none focus:ring-4 focus:ring-brand-100 dark:bg-brand-500 dark:hover:bg-brand-400 dark:focus:ring-brand-500/20"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M21 12 3 20l3-8-3-8 18 8ZM6 12h7"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>

                ارسال تیکت
            </button>
        </div>
    </form>
</x-front.modal>
