<x-front.modal
    :name="$name"
    title="ورود یا ساخت حساب"
    description="برای ادامه خرید، وارد حساب خود شوید یا حساب جدید بسازید."
    maxWidth="max-w-md"
>
    <form
        wire:submit.prevent="submit"
        x-data="{ mode: @entangle('mode') }"
        class="min-w-0 dark:text-slate-100"
    >
        @if($authError)
            <div
                class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 dark:border-red-900/40 dark:bg-red-900/20">
                <div class="flex items-start gap-3">
                    <span
                        class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-white text-red-600 ring-1 ring-red-100 dark:bg-red-950/40 dark:text-red-300 dark:ring-red-900/40">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M12 9v4m0 4h.01M10.3 4.6 2.8 17.5A2 2 0 0 0 4.5 20h15a2 2 0 0 0 1.7-2.5L13.7 4.6a2 2 0 0 0-3.4 0Z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </span>

                    <div class="min-w-0">
                        <div class="text-xs font-black text-red-900 dark:text-red-300">
                            عملیات ناموفق بود
                        </div>

                        <p class="mt-1 text-[11px] font-bold leading-5 text-red-700 dark:text-red-400">
                            {{ $authError }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="mb-4 grid grid-cols-2 gap-2 rounded-2xl bg-slate-200/75 p-1 dark:bg-slate-800">
            <button
                type="button"
                x-on:click="mode = 'login'; $wire.setMode('login')"
                class="cursor-pointer rounded-xl px-3 py-2 text-xs font-extrabold transition"
                :class="mode === 'login'
                    ? 'bg-white text-brand-700 shadow-sm dark:bg-slate-900 dark:text-brand-300'
                    : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
            >
                ورود
            </button>

            <button
                type="button"
                x-on:click="mode = 'register'; $wire.setMode('register')"
                class="cursor-pointer rounded-xl px-3 py-2 text-xs font-extrabold transition"
                :class="mode === 'register'
                    ? 'bg-white text-brand-700 shadow-sm dark:bg-slate-900 dark:text-brand-300'
                    : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
            >
                ثبت نام
            </button>
        </div>

        <div x-show="mode === 'login'" x-cloak>
            <div class="mt-4 space-y-3">
                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">موبایل یا ایمیل</label>

                    <input
                        type="text"
                        wire:model.defer="identifier"
                        class="mt-1.5 w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-left text-sm font-bold outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-brand-500/20"
                        dir="ltr"
                        autocomplete="username"
                    >

                    @error('identifier')
                    <p class="mt-1 text-[11px] font-bold text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">رمز عبور</label>

                    <input
                        type="password"
                        wire:model.defer="password"
                        class="mt-1.5 w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-left text-sm font-bold outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-brand-500/20"
                        dir="ltr"
                        autocomplete="current-password"
                    >

                    @error('password')
                    <p class="mt-1 text-[11px] font-bold text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div x-show="mode === 'register'" x-cloak>
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">
                        <span class="text-red-500">*</span>
                        نام
                    </label>

                    <input
                        type="text"
                        wire:model.defer="firstName"
                        class="mt-1.5 w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-bold outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-brand-500/20"
                        autocomplete="given-name"
                    >

                    @error('firstName')
                    <p class="mt-1 text-[11px] font-bold text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">
                        <span class="text-red-500">*</span>
                        نام خانوادگی
                    </label>

                    <input
                        type="text"
                        wire:model.defer="lastName"
                        class="mt-1.5 w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-bold outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-brand-500/20"
                        autocomplete="family-name"
                    >

                    @error('lastName')
                    <p class="mt-1 text-[11px] font-bold text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">
                        <span class="text-red-500">*</span>
                        شماره موبایل
                    </label>

                    <input
                        type="text"
                        wire:model.defer="mobile"
                        class="mt-1.5 w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-left text-sm font-bold outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-brand-500/20"
                        dir="ltr"
                        autocomplete="tel"
                    >

                    @error('mobile')
                    <p class="mt-1 text-[11px] font-bold text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="min-w-0">
                    <label class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">
                        ایمیل
                        <span class="text-[9px] text-slate-500 dark:text-slate-400">(اختیاری)</span>
                    </label>

                    <input
                        type="text"
                        wire:model.defer="email"
                        class="mt-1.5 w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-left text-sm font-bold outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-brand-500/20"
                        dir="ltr"
                        autocomplete="email"
                    >

                    @error('email')
                    <p class="mt-1 text-[11px] font-bold text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="min-w-0 sm:col-span-2">
                    <label class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">
                        <span class="text-red-500">*</span>
                        رمز عبور
                    </label>

                    <input
                        type="password"
                        wire:model.defer="password"
                        class="mt-1.5 w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-left text-sm font-bold outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-brand-500/20"
                        dir="ltr"
                        autocomplete="new-password"
                    >

                    @error('password')
                    <p class="mt-1 text-[11px] font-bold text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-2 sm:flex sm:items-center sm:justify-end">
            <button
                type="button"
                x-on:click="$dispatch('close-modal', '{{ $name }}')"
                class="h-11 cursor-pointer rounded-2xl border border-slate-200 bg-white px-4 text-xs font-extrabold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
            >
                انصراف
            </button>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="submit"
                class="inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-xs font-extrabold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:shadow-none"
            >
                <span wire:loading.remove wire:target="submit">
                    <span x-show="mode === 'login'">ورود و ادامه</span>
                    <span x-show="mode === 'register'">ثبت نام و ادامه</span>
                </span>

                <span wire:loading wire:target="submit">
                    در حال پردازش...
                </span>

                <span
                    wire:loading
                    wire:target="submit"
                    class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
                ></span>
            </button>
        </div>
    </form>
</x-front.modal>
