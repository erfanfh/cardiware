<div>
    <div
        wire:loading.class="opacity-60"
        class="transition font-sans dark:text-slate-100
            [&_h2]:dark:text-slate-100
            [&_p]:dark:text-slate-400
            [&_label]:dark:text-slate-300
            [&_input]:dark:border-slate-700
            [&_input]:dark:bg-slate-950
            [&_input]:dark:text-slate-100
            [&_input]:dark:placeholder:text-slate-500
            [&_input]:dark:focus:border-orange-400
            [&_button.text-slate-600]:dark:text-slate-300
            [&_a.text-slate-600]:dark:text-slate-300
            [&_.border-orange-100]:dark:border-orange-500/20
            [&_.bg-orange-50]:dark:bg-orange-500/10
            [&_.text-slate-800]:dark:text-slate-100
            [&_.text-slate-700]:dark:text-slate-200
            [&_.text-slate-600]:dark:text-slate-300
            [&_.text-slate-500]:dark:text-slate-400
            [&_.text-slate-400]:dark:text-slate-500"
    >
        @if (session()->has('success'))
            <div
                class="mb-6 rounded-2xl border border-orange-200 bg-gradient-to-r from-orange-50 to-amber-50 px-5 py-4 text-sm text-amber-800 dark:border-orange-500/20 dark:from-orange-500/10 dark:to-amber-500/10 dark:text-amber-300">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                              clip-rule="evenodd"/>
                    </svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div
                class="mb-6 rounded-2xl border border-red-200 bg-gradient-to-r from-red-50 to-orange-50 px-5 py-4 text-sm text-red-700 dark:border-red-500/20 dark:from-red-500/10 dark:to-orange-500/10 dark:text-red-300">
                <div class="flex items-center gap-2 font-bold mb-3">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                              clip-rule="evenodd"/>
                    </svg>
                    خطا در ارسال اطلاعات:
                </div>
                <ul class="space-y-2 pr-5">
                    @foreach ($errors->all() as $error)
                        <li class="flex items-start gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-400 mt-2 flex-shrink-0"></span>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($step === \App\Domain\Identity\Enums\AuthFlowStepEnum::LOGIN)
            @include('livewire.front.identity.user.auth.partials.login')
        @endif

        @if($step === \App\Domain\Identity\Enums\AuthFlowStepEnum::REGISTER)
            @include('livewire.front.identity.user.auth.partials.register')
        @endif

        @if($step === \App\Domain\Identity\Enums\AuthFlowStepEnum::OTP_LOGIN)
            @include('livewire.front.identity.user.auth.partials.otp-login')
        @endif

        @if($step === \App\Domain\Identity\Enums\AuthFlowStepEnum::VERIFY_OTP_LOGIN)
            @include('livewire.front.identity.user.auth.partials.verify-otp-login')
        @endif

        @if($step === \App\Domain\Identity\Enums\AuthFlowStepEnum::ENTER_USERNAME)
            @include('livewire.front.identity.user.auth.partials.enter-username')
        @endif

        @if($step === \App\Domain\Identity\Enums\AuthFlowStepEnum::FORGOT_PASSWORD)
            @include('livewire.front.identity.user.auth.partials.forgot-password')
        @endif
    </div>
</div>
