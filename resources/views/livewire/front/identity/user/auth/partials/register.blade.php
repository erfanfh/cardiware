<div
    class="flex min-w-0 flex-col gap-5"
    x-data="{
        showPassword: false,
        showPasswordConfirmation: false
    }"
>
    @include(
        'livewire.front.identity.user.auth.partials.shared.step-header',
        [
            'title' => 'تکمیل ثبت نام',
            'description' => 'اطلاعات خود و کد تایید ارسال شده را وارد کنید',
            'backMethod' => 'goToEnterMobile',
            'mobile' => $resolvedMobile,
        ]
    )

    <form
        wire:submit.prevent="register"
        class="space-y-4"
        novalidate
    >
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="min-w-0 space-y-1.5">
                <label
                    for="register-first-name"
                    class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
                >
                    <span class="text-red-500">*</span>
                    نام
                </label>

                <input
                    id="register-first-name"
                    type="text"
                    autocomplete="given-name"
                    wire:model.defer="firstName"
                    class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:bg-slate-900 dark:focus:ring-brand-500"
                />
            </div>

            <div class="min-w-0 space-y-1.5">
                <label
                    for="register-last-name"
                    class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
                >
                    <span class="text-red-500">*</span>
                    نام خانوادگی
                </label>

                <input
                    id="register-last-name"
                    type="text"
                    autocomplete="family-name"
                    wire:model.defer="lastName"
                    class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:bg-slate-900 dark:focus:ring-brand-500"
                />
            </div>
        </div>

        <div class="space-y-1.5">
            <label
                for="register-email"
                class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
            >
                ایمیل

                <span class="text-[0.7rem] font-normal text-slate-400 dark:text-slate-500">
                    اختیاری
                </span>
            </label>

            <input
                id="register-email"
                type="email"
                dir="ltr"
                autocomplete="email"
                wire:model.defer="email"
                class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-left text-sm outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:bg-slate-900 dark:focus:ring-brand-500"
            />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="min-w-0 space-y-1.5">
                <label
                    for="register-password"
                    class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
                >
                    <span class="text-red-500">*</span>
                    رمز عبور
                </label>

                <div class="relative">
                    <input
                        id="register-password"
                        :type="showPassword ? 'text' : 'password'"
                        dir="ltr"
                        autocomplete="new-password"
                        wire:model.defer="password"
                        class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 pl-10 text-left text-sm outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:bg-slate-900 dark:focus:ring-brand-500"
                    />

                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute left-3.5 top-1/2 -translate-y-1/2 cursor-pointer text-slate-400 transition-colors hover:text-brand-600 dark:text-slate-500 dark:hover:text-brand-400"
                    >
                        <span x-text="showPassword ? '◉' : '◎'"></span>
                    </button>
                </div>
            </div>

            <div class="min-w-0 space-y-1.5">
                <label
                    for="register-password-confirmation"
                    class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
                >
                    <span class="text-red-500">*</span>
                    تکرار رمز عبور
                </label>

                <div class="relative">
                    <input
                        id="register-password-confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        dir="ltr"
                        autocomplete="new-password"
                        wire:model.defer="passwordConfirmation"
                        class="no-autofill-bg h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 pl-10 text-left text-sm outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:bg-slate-900 dark:focus:ring-brand-500"
                    />

                    <button
                        type="button"
                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                        class="absolute left-3.5 top-1/2 -translate-y-1/2 cursor-pointer text-slate-400 transition-colors hover:text-brand-600 dark:text-slate-500 dark:hover:text-brand-400"
                    >
                        <span x-text="showPasswordConfirmation ? '◉' : '◎'"></span>
                    </button>
                </div>
            </div>
        </div>

        <div class="space-y-1.5">
            <label
                for="register-otp-code"
                class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
            >
                <span class="text-red-500">*</span>
                کد تایید
            </label>

            <input
                id="register-otp-code"
                type="text"
                dir="ltr"
                inputmode="numeric"
                pattern="[0-9]*"
                autocomplete="one-time-code"
                maxlength="{{ (int) config('auth-flow.otp.length', 6) }}"
                wire:model.defer="otpCode"
                placeholder="_ _ _ _ _ _"
                class="no-autofill-bg h-12 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-center text-base font-semibold tracking-[0.35em] outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:bg-slate-900 dark:focus:ring-brand-500 sm:tracking-[0.45em]"
            />
        </div>

        @include(
            'livewire.front.identity.user.auth.partials.shared.otp-resend'
        )

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="register"
            class="relative flex h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-brand-500 px-4 text-sm font-semibold text-white shadow-lg shadow-brand-500/20 transition-all duration-300 hover:bg-brand-600 hover:shadow-brand-500/30 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70"
        >
            <span
                wire:loading.remove
                wire:target="register"
            >
                ایجاد حساب کاربری
            </span>

            <svg
                wire:loading
                wire:target="register"
                class="h-5 w-5 animate-spin"
                viewBox="0 0 24 24"
                fill="none"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />

                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"
                />
            </svg>
        </button>
    </form>
</div>
