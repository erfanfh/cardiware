<div
    x-data="{
        errorObserver: null,
        errorScrollTimer: null,

        init() {
            this.errorObserver = new MutationObserver(() => {
                this.scheduleErrorScroll();
            });

            this.errorObserver.observe(this.$el, {
                childList: true,
                subtree: true,
                characterData: true,
            });

            this.$nextTick(() => {
                this.scrollToErrors();
            });
        },

        destroy() {
            if (this.errorObserver) {
                this.errorObserver.disconnect();
            }

            clearTimeout(this.errorScrollTimer);
        },

        scheduleErrorScroll() {
            clearTimeout(this.errorScrollTimer);

            this.errorScrollTimer = setTimeout(() => {
                this.$nextTick(() => {
                    this.scrollToErrors();
                });
            }, 100);
        },

        scrollToErrors() {
            const errorBox = this.$el.querySelector(
                '[data-auth-flow-errors]'
            );

            if (!errorBox) {
                return;
            }

            const rect = errorBox.getBoundingClientRect();

            const isVisible =
                rect.top >= 80 &&
                rect.top <= window.innerHeight - 80;

            if (isVisible) {
                return;
            }

            errorBox.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
                inline: 'nearest',
            });

            setTimeout(() => {
                errorBox.focus({
                    preventScroll: true,
                });
            }, 400);
        },
    }"
>
    <div
        wire:loading.class="pointer-events-none opacity-60"
        wire:target="startAuthFlow,loginWithPassword,loginWithEmail,goToEmailLogin,requestLoginOtp,requestPasswordResetOtp,verifyLoginOtp,register,resetPassword,resendOtp"
        class="font-sans transition
            dark:text-slate-100
            [&_h2]:dark:text-slate-100
            [&_p]:dark:text-slate-400
            [&_label]:dark:text-slate-300
            [&_input]:dark:border-slate-700
            [&_input]:dark:bg-slate-950
            [&_input]:dark:text-slate-100
            [&_input]:dark:placeholder:text-slate-500"
    >
        @if(session()->has('success'))
            <div
                class="mb-6 rounded-2xl border border-orange-200 bg-gradient-to-r from-orange-50 to-amber-50 px-5 py-4 text-sm text-amber-800 dark:border-orange-500/20 dark:from-orange-500/10 dark:to-amber-500/10 dark:text-amber-300">
                <div class="flex items-center gap-2">
                    <svg
                        class="h-5 w-5 shrink-0"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.707-9.293a1 1 0 0 0-1.414-1.414L9 10.586 7.707 9.293a1 1 0 0 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l4-4Z"
                            clip-rule="evenodd"
                        />
                    </svg>

                    <span class="font-bold">
                        {{ session('success') }}
                    </span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div
                data-auth-flow-errors
                tabindex="-1"
                role="alert"
                aria-live="assertive"
                class="mb-6 scroll-mt-24 rounded-2xl border border-red-200 bg-gradient-to-r from-red-50 to-orange-50 px-5 py-4 text-sm text-red-700 outline-none dark:border-red-500/20 dark:from-red-500/10 dark:to-orange-500/10 dark:text-red-300"
            >
                <div class="mb-3 flex items-center gap-2 font-bold">
                    <svg
                        class="h-5 w-5 shrink-0"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.707 7.293a1 1 0 0 0-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 1 0 1.414 1.414L10 11.414l1.293 1.293a1 1 0 0 0 1.414-1.414L11.414 10l1.293-1.293a1 1 0 0 0-1.414-1.414L10 8.586 8.707 7.293Z"
                            clip-rule="evenodd"
                        />
                    </svg>

                    خطا در ارسال اطلاعات
                </div>

                <ul class="space-y-2 pr-5">
                    @foreach($errors->all() as $error)
                        <li class="flex items-start gap-2">
                            <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-red-400"></span>

                            <span>{{ $error }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div wire:key="auth-flow-step-{{ $step->value }}">
            @switch($step)
                @case(\App\Domain\Identity\Enums\AuthFlowStepEnum::ENTER_MOBILE)
                    @include(
                        'livewire.front.identity.user.auth.partials.enter-mobile'
                    )
                    @break

                @case(\App\Domain\Identity\Enums\AuthFlowStepEnum::LOGIN_EMAIL)
                    @include('livewire.front.identity.user.auth.partials.login-email')
                    @break

                @case(\App\Domain\Identity\Enums\AuthFlowStepEnum::LOGIN_PASSWORD)
                    @include(
                        'livewire.front.identity.user.auth.partials.login-password'
                    )
                    @break

                @case(\App\Domain\Identity\Enums\AuthFlowStepEnum::VERIFY_LOGIN_OTP)
                    @include(
                        'livewire.front.identity.user.auth.partials.verify-login-otp'
                    )
                    @break

                @case(\App\Domain\Identity\Enums\AuthFlowStepEnum::REGISTER)
                    @include(
                        'livewire.front.identity.user.auth.partials.register'
                    )
                    @break

                @case(\App\Domain\Identity\Enums\AuthFlowStepEnum::RESET_PASSWORD)
                    @include(
                        'livewire.front.identity.user.auth.partials.reset-password'
                    )
                    @break
            @endswitch
        </div>
    </div>
</div>
