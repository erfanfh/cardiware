<div
    class="text-center"
    x-data="{
        now: Math.floor(Date.now() / 1000),
        resendAt: Number(@js($otpResendAvailableAt)),
        expiresAt: Number(@js($otpExpiresAt)),
        timer: null,

        start() {
            this.stop();
            this.tick();

            this.timer = setInterval(() => {
                this.tick();
            }, 1000);
        },

        stop() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },

        tick() {
            this.now = Math.floor(Date.now() / 1000);
        },

        sync(detail) {
            this.resendAt = Number(
                detail.resendAvailableAt ?? 0
            );

            this.expiresAt = Number(
                detail.expiresAt ?? 0
            );

            this.start();
        },

        format(seconds) {
            const value = Math.max(0, Number(seconds));
            const minutes = Math.floor(value / 60);
            const remainingSeconds = value % 60;

            return `${minutes}:${String(remainingSeconds).padStart(2, '0')}`;
        },

        get resendSeconds() {
            return Math.max(
                0,
                this.resendAt - this.now
            );
        },

        get expirySeconds() {
            return Math.max(
                0,
                this.expiresAt - this.now
            );
        }
    }"
    x-init="start()"
    @otp-timing-updated.window="sync($event.detail)"
>
    <div class="mb-3 text-xs text-slate-500 dark:text-slate-400">
        <p
            x-cloak
            x-show="expirySeconds > 0"
        >
            Code validity:
            <span
                dir="ltr"
                class="font-semibold text-slate-700 dark:text-slate-300"
                x-text="format(expirySeconds)"
            ></span>
        </p>

        <p
            x-cloak
            x-show="expirySeconds === 0"
            class="text-amber-600 dark:text-amber-400"
        >
            The code has expired.
        </p>
    </div>

    <button
        type="button"
        wire:click="resendOtp"
        wire:loading.attr="disabled"
        wire:target="resendOtp"
        :disabled="resendSeconds > 0"
        class="inline-flex min-h-8 items-center justify-center gap-1.5 text-xs font-semibold transition-colors disabled:cursor-not-allowed"
        :class="resendSeconds > 0
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

        <svg
            wire:loading
            wire:target="resendOtp"
            class="h-4 w-4 animate-spin"
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

        <span
            x-cloak
            x-show="resendSeconds === 0"
        >
            Resend code
        </span>

        <span
            x-cloak
            x-show="resendSeconds > 0"
            class="inline-flex items-center gap-1"
        >
            <span
                dir="ltr"
                x-text="format(resendSeconds)"
            ></span>

            <span>until resend</span>
        </span>
    </button>
</div>
