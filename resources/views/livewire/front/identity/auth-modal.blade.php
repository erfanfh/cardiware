<x-front.modal
    :name="$name"
    title="ورود یا ساخت حساب"
    description="برای ادامه، مراحل ورود یا ثبت نام را تکمیل کنید."
    :maxWidth="$step === \App\Domain\Identity\Enums\AuthFlowStepEnum::REGISTER
        ? 'max-w-2xl'
        : 'max-w-md'"
>
    <div
        x-data="{
            observer: null,
            errorScrollTimer: null,
            lastErrorFingerprint: '',

            init() {
                this.observer = new MutationObserver(() => {
                    this.checkErrorState();
                });

                this.observer.observe(this.$el, {
                    childList: true,
                    subtree: true,
                });

                this.$nextTick(() => {
                    this.checkErrorState();
                });
            },

            destroy() {
                if (this.observer) {
                    this.observer.disconnect();
                }

                clearTimeout(this.errorScrollTimer);
            },

            checkErrorState() {
                const errorBox = this.$el.querySelector(
                    '[data-auth-modal-errors]'
                );

                if (!errorBox) {
                    this.lastErrorFingerprint = '';
                    return;
                }

                const fingerprint = errorBox.textContent.trim();

                if (
                    !fingerprint ||
                    fingerprint === this.lastErrorFingerprint
                ) {
                    return;
                }

                this.lastErrorFingerprint = fingerprint;

                clearTimeout(this.errorScrollTimer);

                this.errorScrollTimer = setTimeout(() => {
                    this.$nextTick(() => {
                        this.scrollToErrorBox(errorBox);
                    });
                }, 100);
            },

            scrollToErrorBox(errorBox) {
                if (!errorBox || !errorBox.isConnected) {
                    return;
                }

                const rect = errorBox.getBoundingClientRect();

                const isVisible =
                    rect.top >= 80 &&
                    rect.bottom <= window.innerHeight - 40;

                if (!isVisible) {
                    errorBox.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                        inline: 'nearest',
                    });
                }

                setTimeout(() => {
                    errorBox.focus({
                        preventScroll: true,
                    });
                }, 350);
            },

            closeModal() {
                this.$dispatch(
                    'close-modal',
                    @js($name)
                );
            },
        }"
        x-on:close-modal.window="
            const modalName = $event.detail?.name ?? $event.detail;

            if (modalName === @js($name)) {
                $wire.resetAuthFlow();
            }
        "
        wire:loading.class="pointer-events-none opacity-60"
        wire:target="startAuthFlow,loginWithPassword,requestLoginOtp,requestPasswordResetOtp,verifyLoginOtp,register,resetPassword,resendOtp"
        class="min-w-0 transition dark:text-slate-100"
    >
        @if(session()->has('success'))
            <div
                role="status"
                aria-live="polite"
                class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-900/40 dark:bg-emerald-900/20"
            >
                <div class="flex items-start gap-3">
                    <span
                        class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-white text-emerald-600 ring-1 ring-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-900/40"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <path
                                d="m5 12 4 4L19 6"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </span>

                    <div class="min-w-0">
                        <div class="text-xs font-black text-emerald-900 dark:text-emerald-300">
                            عملیات موفق بود
                        </div>

                        <p class="mt-1 text-[11px] font-bold leading-5 text-emerald-700 dark:text-emerald-400">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @include(
            'livewire.front.identity.auth-modal.partials.shared.error-box'
        )

        <div
            wire:key="auth-modal-step-{{ $name }}-{{ $step->value }}"
        >
            @switch($step)
                @case(\App\Domain\Identity\Enums\AuthFlowStepEnum::ENTER_MOBILE)
                    @include(
                        'livewire.front.identity.auth-modal.partials.enter-mobile'
                    )
                    @break

                @case(\App\Domain\Identity\Enums\AuthFlowStepEnum::LOGIN_PASSWORD)
                    @include(
                        'livewire.front.identity.auth-modal.partials.login-password'
                    )
                    @break

                @case(\App\Domain\Identity\Enums\AuthFlowStepEnum::VERIFY_LOGIN_OTP)
                    @include(
                        'livewire.front.identity.auth-modal.partials.verify-login-otp'
                    )
                    @break

                @case(\App\Domain\Identity\Enums\AuthFlowStepEnum::REGISTER)
                    @include(
                        'livewire.front.identity.auth-modal.partials.register'
                    )
                    @break

                @case(\App\Domain\Identity\Enums\AuthFlowStepEnum::RESET_PASSWORD)
                    @include(
                        'livewire.front.identity.auth-modal.partials.reset-password'
                    )
                    @break
            @endswitch
        </div>
    </div>
</x-front.modal>
