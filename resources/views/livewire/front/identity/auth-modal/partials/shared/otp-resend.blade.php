<div
    class="text-center"
    x-data="{
        currentTimestamp: Math.floor(Date.now() / 1000),
        resendAvailableAt: Number(@js($otpResendAvailableAt)),
        expiresAt: Number(@js($otpExpiresAt)),
        timer: null,

        init() {
            this.startTimer();
        },

        destroy() {
            this.stopTimer();
        },

        startTimer() {
            this.stopTimer();

            this.updateCurrentTime();

            this.timer = setInterval(() => {
                this.updateCurrentTime();
            }, 1000);
        },

        stopTimer() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },

        updateCurrentTime() {
            this.currentTimestamp = Math.floor(
                Date.now() / 1000
            );
        },

        syncTiming(detail) {
            if (
                detail.modalName !== @js($name)
            ) {
                return;
            }

            this.resendAvailableAt = Number(
                detail.resendAvailableAt ?? 0
            );

            this.expiresAt = Number(
                detail.expiresAt ?? 0
            );

            this.startTimer();
        },

        formatTime(seconds) {
            const remaining = Math.max(
                0,
                Number(seconds)
            );

            const minutes = Math.floor(
                remaining / 60
            );

            const remainingSeconds = remaining % 60;

            return `${minutes}:${String(remainingSeconds).padStart(2, '0')}`;
        },

        get resendSeconds() {
            return Math.max(
                0,
                this.resendAvailableAt - this.currentTimestamp
            );
        },

        get expirySeconds() {
            return Math.max(
                0,
                this.expiresAt - this.currentTimestamp
            );
        },
    }"
    x-on:auth-modal-otp-timing-updated.window="
        syncTiming($event.detail)
    "
>
    <div class="mb-2 text-[11px] font-bold text-slate-500 dark:text-slate-400">
        <span
            x-cloak
            x-show="expirySeconds > 0"
        >
            اعتبار کد:

            <span
                dir="ltr"
                class="font-black text-slate-700 dark:text-slate-300"
                x-text="formatTime(expirySeconds)"
            ></span>
        </span>

        <span
            x-cloak
            x-show="expirySeconds === 0"
            class="text-amber-600 dark:text-amber-400"
        >
            اعتبار کد به پایان رسیده است.
        </span>
    </div>

    <button
        type="button"
        wire:click="resendOtp"
        wire:loading.attr="disabled"
        wire:target="resendOtp"
        x-bind:disabled="resendSeconds > 0"
        class="inline-flex min-h-8 items-center justify-center gap-1.5 text-[11px] font-extrabold transition disabled:cursor-not-allowed"
        x-bind:class="resendSeconds > 0
            ? 'text-slate-400 dark:text-slate-600'
            : 'cursor-pointer text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300'"
    >
        <svg
            wire:loading.remove
            wire:target="resendOtp"
            class="h-3.5 w-3.5"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
        >
            <path d="M23 4v6h-6"/>
            <path d="M1 20v-6h6"/>
            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10"/>
            <path d="M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
        </svg>

        <span
            wire:loading.remove
            wire:target="resendOtp"
            x-cloak
            x-show="resendSeconds === 0"
        >
            ارسال مجدد کد
        </span>

        <span
            wire:loading.remove
            wire:target="resendOtp"
            x-cloak
            x-show="resendSeconds > 0"
            class="inline-flex items-center gap-1"
        >
            <span
                dir="ltr"
                x-text="formatTime(resendSeconds)"
            ></span>

            <span>تا ارسال مجدد</span>
        </span>

        <span
            wire:loading
            wire:target="resendOtp"
            class="inline-flex items-center gap-1.5"
        >
            <span
                class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-brand-300/40 border-t-brand-600"
            ></span>

            در حال ارسال
        </span>
    </button>
</div>
