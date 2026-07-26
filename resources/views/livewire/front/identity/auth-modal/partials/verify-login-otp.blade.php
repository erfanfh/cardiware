<div>
    @include(
        'livewire.front.identity.auth-modal.partials.shared.step-header',
        [
            'title' => 'تایید ورود',
            'description' => 'کد ارسال شده به شماره موبایل را وارد کنید.',
            'backMethod' => 'goToPasswordLogin',
            'mobile' => $resolvedMobile,
        ]
    )

    <form
        wire:submit.prevent="verifyLoginOtp"
        novalidate
        class="space-y-4"
    >
        <div class="min-w-0">
            <label
                for="{{ $name }}-login-otp"
                class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300"
            >
                کد تایید {{ (int) config('auth-flow.otp.length', 6) }} رقمی
            </label>

            <input
                id="{{ $name }}-login-otp"
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
                wire:target="verifyLoginOtp"
                class="inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-6 text-xs font-extrabold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:shadow-none"
            >
                <span
                    wire:loading.remove
                    wire:target="verifyLoginOtp"
                >
                    تایید و ورود
                </span>

                <span
                    wire:loading
                    wire:target="verifyLoginOtp"
                >
                    در حال بررسی...
                </span>

                <span
                    wire:loading
                    wire:target="verifyLoginOtp"
                    class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
                ></span>
            </button>
        </div>
    </form>
</div>
