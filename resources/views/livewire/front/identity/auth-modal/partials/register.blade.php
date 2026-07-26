<div
    x-data="{
        showPassword: false,
        showPasswordConfirmation: false,
    }"
>
    @include(
        'livewire.front.identity.auth-modal.partials.shared.step-header',
        [
            'title' => 'تکمیل ثبت نام',
            'description' => 'اطلاعات خود و کد تایید ارسال شده را وارد کنید.',
            'backMethod' => 'goToEnterMobile',
            'mobile' => $resolvedMobile,
        ]
    )

    <form
        wire:submit.prevent="register"
        novalidate
        class="space-y-4"
    >
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="min-w-0">
                <label
                    for="{{ $name }}-first-name"
                    class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300"
                >
                    <span class="text-red-500">*</span>
                    نام
                </label>

                <input
                    id="{{ $name }}-first-name"
                    type="text"
                    autocomplete="given-name"
                    wire:model.defer="firstName"
                    class="mt-1.5 w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-bold outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-500/20"
                >

                @include(
                    'livewire.front.identity.auth-modal.partials.shared.field-error',
                    ['field' => 'firstName']
                )
            </div>

            <div class="min-w-0">
                <label
                    for="{{ $name }}-last-name"
                    class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300"
                >
                    <span class="text-red-500">*</span>
                    نام خانوادگی
                </label>

                <input
                    id="{{ $name }}-last-name"
                    type="text"
                    autocomplete="family-name"
                    wire:model.defer="lastName"
                    class="mt-1.5 w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-bold outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-500/20"
                >

                @include(
                    'livewire.front.identity.auth-modal.partials.shared.field-error',
                    ['field' => 'lastName']
                )
            </div>
        </div>

        <div class="min-w-0">
            <label
                for="{{ $name }}-email"
                class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300"
            >
                ایمیل

                <span class="text-[9px] text-slate-400 dark:text-slate-500 mr-2">
                    اختیاری
                </span>
            </label>

            <input
                id="{{ $name }}-email"
                type="email"
                dir="ltr"
                autocomplete="email"
                wire:model.defer="email"
                class="mt-1.5 w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-left text-sm font-bold outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-500/20"
            >

            @include(
                'livewire.front.identity.auth-modal.partials.shared.field-error',
                ['field' => 'email']
            )
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="min-w-0">
                <label
                    for="{{ $name }}-register-password"
                    class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300"
                >
                    <span class="text-red-500">*</span>
                    رمز عبور
                </label>

                <div class="relative mt-1.5">
                    <input
                        id="{{ $name }}-register-password"
                        x-bind:type="showPassword ? 'text' : 'password'"
                        dir="ltr"
                        autocomplete="new-password"
                        wire:model.defer="password"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 pl-16 text-left text-sm font-bold outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-500/20"
                    >

                    <button
                        type="button"
                        x-on:click="showPassword = !showPassword"
                        class="absolute left-3.5 top-1/2 -translate-y-1/2 cursor-pointer text-[10px] font-extrabold text-slate-400 hover:text-brand-600 dark:text-slate-500 dark:hover:text-brand-400"
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

            <div class="min-w-0">
                <label
                    for="{{ $name }}-register-password-confirmation"
                    class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300"
                >
                    <span class="text-red-500">*</span>
                    تکرار رمز عبور
                </label>

                <div class="relative mt-1.5">
                    <input
                        id="{{ $name }}-register-password-confirmation"
                        x-bind:type="showPasswordConfirmation ? 'text' : 'password'"
                        dir="ltr"
                        autocomplete="new-password"
                        wire:model.defer="passwordConfirmation"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 pl-16 text-left text-sm font-bold outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-500/20"
                    >

                    <button
                        type="button"
                        x-on:click="showPasswordConfirmation = !showPasswordConfirmation"
                        class="absolute left-3.5 top-1/2 -translate-y-1/2 cursor-pointer text-[10px] font-extrabold text-slate-400 hover:text-brand-600 dark:text-slate-500 dark:hover:text-brand-400"
                    >
                        <svg
                            x-show="!showPasswordConfirmation"
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
                            x-show="showPasswordConfirmation"
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
                    ['field' => 'passwordConfirmation']
                )
            </div>
        </div>

        <div class="min-w-0">
            <label
                for="{{ $name }}-register-otp"
                class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300"
            >
                <span class="text-red-500">*</span>
                کد تایید
            </label>

            <input
                id="{{ $name }}-register-otp"
                type="text"
                dir="ltr"
                inputmode="numeric"
                pattern="[0-9]*"
                autocomplete="one-time-code"
                maxlength="{{ (int) config('auth-flow.otp.length', 6) }}"
                wire:model.defer="otpCode"
                placeholder="_ _ _ _ _ _"
                class="mt-1.5 h-12 w-full rounded-2xl border border-slate-200 bg-white px-3.5 text-center text-base font-black tracking-[0.4em] outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-500/20"
            >

            @include(
                'livewire.front.identity.auth-modal.partials.shared.field-error',
                ['field' => 'otpCode']
            )
        </div>

        @include(
            'livewire.front.identity.auth-modal.partials.shared.otp-resend'
        )

        <div class="grid grid-cols-1 gap-2 sm:flex sm:items-center sm:justify-end">
            <button
                type="button"
                x-on:click="closeModal()"
                class="h-11 cursor-pointer rounded-2xl border border-slate-200 bg-white px-4 text-xs font-extrabold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
            >
                انصراف
            </button>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="register"
                class="inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-6 text-xs font-extrabold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:shadow-none"
            >
                <span
                    wire:loading.remove
                    wire:target="register"
                >
                    ثبت نام و ادامه
                </span>

                <span
                    wire:loading
                    wire:target="register"
                >
                    در حال ساخت حساب...
                </span>

                <span
                    wire:loading
                    wire:target="register"
                    class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
                ></span>
            </button>
        </div>
    </form>
</div>
