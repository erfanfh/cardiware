<div x-data="{ showPassword: false }">
    @include(
        'livewire.front.identity.auth-modal.partials.shared.step-header',
        [
            'title' => 'ورود به حساب',
            'description' => 'رمز عبور حساب خود را وارد کنید.',
            'backMethod' => 'goToEnterMobile',
            'mobile' => $resolvedMobile,
        ]
    )

    <form
        wire:submit.prevent="loginWithPassword"
        novalidate
        class="space-y-4"
    >
        <div class="min-w-0">
            <div class="flex items-center justify-between gap-3">
                <label
                    for="{{ $name }}-login-password"
                    class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300"
                >
                    رمز عبور
                </label>

                <button
                    type="button"
                    wire:click="requestPasswordResetOtp"
                    wire:loading.attr="disabled"
                    wire:target="requestPasswordResetOtp"
                    class="cursor-pointer text-[11px] font-extrabold text-slate-500 transition hover:text-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:text-slate-400 dark:hover:text-brand-400"
                >
                    <span
                        wire:loading.remove
                        wire:target="requestPasswordResetOtp"
                    >
                        فراموشی رمز؟
                    </span>

                    <span
                        wire:loading
                        wire:target="requestPasswordResetOtp"
                    >
                        در حال ارسال...
                    </span>
                </button>
            </div>

            <div class="relative mt-1.5">
                <input
                    id="{{ $name }}-login-password"
                    x-bind:type="showPassword ? 'text' : 'password'"
                    dir="ltr"
                    autocomplete="current-password"
                    wire:model.defer="password"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 pl-16 text-left text-sm font-bold outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-500/20"
                >

                <button
                    type="button"
                    x-on:click="showPassword = !showPassword"
                    class="absolute left-3.5 top-1/2 -translate-y-1/2 cursor-pointer text-[10px] font-extrabold text-slate-400 transition hover:text-brand-600 dark:text-slate-500 dark:hover:text-brand-400"
                    aria-label="نمایش یا مخفی کردن رمز عبور"
                >
                    <svg
                        x-show="!showPassword"
                        xmlns="http://www.w3.org/2000/svg"
                        width="17"
                        height="17"
                        viewBox="0 0 24 24"
                    >
                        <g
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path
                                d="M21.544 11.045c.304.426.456.64.456.955c0 .316-.152.529-.456.955C20.178 14.871 16.689 19 12 19c-4.69 0-8.178-4.13-9.544-6.045C2.152 12.529 2 12.315 2 12c0-.316.152-.529.456-.955C3.822 9.129 7.311 5 12 5c4.69 0 8.178 4.13 9.544 6.045Z"
                            />
                            <path d="M15 12a3 3 0 1 0-6 0a3 3 0 0 0 6 0Z"/>
                        </g>
                    </svg>

                    <svg
                        x-cloak
                        x-show="showPassword"
                        xmlns="http://www.w3.org/2000/svg"
                        width="17"
                        height="17"
                        viewBox="0 0 24 24"
                    >
                        <g
                            fill="none"
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-width="1.5"
                        >
                            <path
                                stroke-linejoin="round"
                                d="M19.439 15.439a19.5 19.5 0 0 0 2.105-2.484c.304-.426.456-.64.456-.955c0-.316-.152-.529-.456-.955C20.178 9.129 16.689 5 12 5c-.908 0-1.77.155-2.582.418m-2.67 1.33c-2.017 1.36-3.506 3.195-4.292 4.297c-.304.426-.456.64-.456.955c0 .316.152.529.456.955C3.822 14.871 7.311 19 12 19c1.99 0 3.765-.744 5.253-1.747"
                            />
                            <path d="M9.858 10A2.929 2.929 0 1 0 14 14.142"/>
                            <path
                                stroke-linejoin="round"
                                d="m3 3l18 18"
                            />
                        </g>
                    </svg>
                </button>
            </div>

            @include(
                'livewire.front.identity.auth-modal.partials.shared.field-error',
                ['field' => 'password']
            )
        </div>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="loginWithPassword"
            class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-xs font-extrabold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:shadow-none"
        >
            <span
                wire:loading.remove
                wire:target="loginWithPassword"
            >
                ورود و ادامه
            </span>

            <span
                wire:loading
                wire:target="loginWithPassword"
            >
                در حال ورود...
            </span>

            <span
                wire:loading
                wire:target="loginWithPassword"
                class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
            ></span>
        </button>

        <div class="relative py-1">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200 dark:border-slate-800"></div>
            </div>

            <div class="relative flex justify-center">
                <span class="bg-white px-3 text-[10px] font-bold text-slate-400 dark:bg-slate-900 dark:text-slate-500">
                    یا
                </span>
            </div>
        </div>

        <button
            type="button"
            wire:click="requestLoginOtp"
            wire:loading.attr="disabled"
            wire:target="requestLoginOtp"
            class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 text-xs font-extrabold text-slate-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        >
            <span
                wire:loading.remove
                wire:target="requestLoginOtp"
            >
                ورود با کد یکبار مصرف
            </span>

            <span
                wire:loading
                wire:target="requestLoginOtp"
            >
                در حال ارسال کد...
            </span>

            <span
                wire:loading
                wire:target="requestLoginOtp"
                class="h-4 w-4 animate-spin rounded-full border-2 border-brand-300/40 border-t-brand-600"
            ></span>
        </button>

        <button
            type="button"
            x-on:click="closeModal()"
            class="h-10 w-full cursor-pointer text-[11px] font-extrabold text-slate-400 transition hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-300"
        >
            انصراف
        </button>
    </form>
</div>
