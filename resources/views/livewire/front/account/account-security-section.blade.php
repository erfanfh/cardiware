<section
    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20">
    <div class="p-4 sm:p-5">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white">Account security</div>
                <div class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                    Change your password using your current password or mobile verification.
                </div>
            </div>

            <div
                wire:loading
                wire:target="save,switchMethod,requestOtp,resendOtp,saveWithOtp"
                class="text-xs font-extrabold text-brand-700 dark:text-brand-300"
            >
                Processing...
            </div>
        </div>

        @if($successMessage)
            <div
                role="status"
                class="mt-4 flex items-start gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-bold leading-6 text-emerald-800 sm:mt-5 sm:p-4 sm:text-sm dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>
                <span>{{ $successMessage }}</span>
            </div>
        @endif

        @if($errorMessage)
            <div
                role="alert"
                class="mt-4 flex items-start gap-2 rounded-2xl border border-red-200 bg-red-50 p-3 text-xs font-bold leading-6 text-red-800 sm:mt-5 sm:p-4 sm:text-sm dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 8v5M12 16h.01"/>
                </svg>
                <span>{{ $errorMessage }}</span>
            </div>
        @endif

        <div class="mt-4 grid grid-cols-1 gap-3 sm:mt-5 sm:grid-cols-2" role="group" aria-label="Verification method">
            <button
                type="button"
                wire:click="switchMethod('current_password')"
                wire:loading.attr="disabled"
                aria-pressed="{{ $method === 'current_password' ? 'true' : 'false' }}"
                class="group flex cursor-pointer items-center gap-3 rounded-2xl border p-3.5 text-start transition disabled:cursor-not-allowed disabled:opacity-60 sm:p-4
                {{ $method === 'current_password'
                    ? 'border-brand-300 bg-brand-50 ring-2 ring-brand-100 dark:border-brand-500/50 dark:bg-brand-500/10 dark:ring-brand-500/10'
                    : 'border-slate-200 bg-slate-50 hover:border-slate-300 hover:bg-white dark:border-slate-700 dark:bg-slate-800/70 dark:hover:border-slate-600 dark:hover:bg-slate-800' }}"
            >
                <span
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                    {{ $method === 'current_password'
                        ? 'bg-brand-500 text-white'
                        : 'bg-white text-slate-500 dark:bg-slate-700 dark:text-slate-300' }}"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="10" width="16" height="10" rx="2"/>
                        <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                    </svg>
                </span>
                <span class="min-w-0">
                    <span class="block text-sm font-extrabold text-slate-900 dark:text-white">I know my current password</span>
                    <span class="mt-0.5 block text-[11px] font-bold leading-5 text-slate-500 dark:text-slate-400">
                        Quick verification with current password
                    </span>
                </span>
            </button>

            <button
                type="button"
                wire:click="switchMethod('otp')"
                wire:loading.attr="disabled"
                aria-pressed="{{ $method === 'otp' ? 'true' : 'false' }}"
                class="group flex cursor-pointer items-center gap-3 rounded-2xl border p-3.5 text-start transition disabled:cursor-not-allowed disabled:opacity-60 sm:p-4
                {{ $method === 'otp'
                    ? 'border-brand-300 bg-brand-50 ring-2 ring-brand-100 dark:border-brand-500/50 dark:bg-brand-500/10 dark:ring-brand-500/10'
                    : 'border-slate-200 bg-slate-50 hover:border-slate-300 hover:bg-white dark:border-slate-700 dark:bg-slate-800/70 dark:hover:border-slate-600 dark:hover:bg-slate-800' }}"
            >
                <span
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                    {{ $method === 'otp'
                        ? 'bg-brand-500 text-white'
                        : 'bg-white text-slate-500 dark:bg-slate-700 dark:text-slate-300' }}"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="7" y="2" width="10" height="20" rx="2"/>
                        <path d="M11 18h2"/>
                    </svg>
                </span>
                <span class="min-w-0">
                    <span
                        class="block text-sm font-extrabold text-slate-900 dark:text-white">I forgot my current password</span>
                    <span class="mt-0.5 block text-[11px] font-bold leading-5 text-slate-500 dark:text-slate-400">
                        Secure verification with a one-time code
                    </span>
                </span>
            </button>
        </div>
    </div>

    @if($method === 'current_password')
        <form wire:submit.prevent="save" wire:key="current-password-form"
              class="border-t border-slate-200 p-4 sm:p-5 dark:border-slate-800">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="min-w-0">
                    <label for="account-current-password"
                           class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">Current password</label>
                    <input
                        id="account-current-password"
                        type="password"
                        wire:model.defer="current_password"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-sm font-bold outline-none transition focus:ring-4
                        @error('current_password')
                            border-red-300 bg-red-50 text-slate-800 focus:border-red-300 focus:ring-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100 dark:focus:ring-red-500/20
                        @else
                            border-slate-200 bg-slate-50 text-slate-800 placeholder:text-slate-400 focus:border-brand-300 focus:bg-white focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-brand-500 dark:focus:bg-slate-800 dark:focus:ring-brand-500/20
                        @enderror"
                    >
                    @error('current_password')
                    <div class="mt-2 text-xs font-bold leading-5 text-red-600 dark:text-red-300">{{ $message }}</div>
                    @enderror
                </div>

                @include('livewire.front.account.partials.password-field', [
                    'id' => 'account-new-password',
                    'field' => 'password',
                    'label' => 'New password',
                    'placeholder' => 'At least 8 characters',
                ])

                @include('livewire.front.account.partials.password-field', [
                    'id' => 'account-new-password-confirmation',
                    'field' => 'password_confirmation',
                    'label' => 'Confirm new password',
                    'placeholder' => 'Re-enter your new password',
                ])
            </div>

            <div
                class="mt-5 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:flex-row md:items-center md:justify-between dark:border-slate-700 dark:bg-slate-800/70">
                <div class="min-w-0">
                    <div class="text-sm font-extrabold text-slate-900 dark:text-white">Choose a different password</div>
                    <div class="mt-1 text-xs font-bold leading-6 text-slate-500 dark:text-slate-400">
                        New password must be at least 8 characters and different from the current one.
                    </div>
                </div>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:hover:bg-brand-400 md:w-auto"
                >
                    <span wire:loading.remove wire:target="save">Save new password</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </form>
    @elseif(! $otpSent)
        <div wire:key="otp-request" class="border-t border-slate-200 p-4 sm:p-5 dark:border-slate-800">
            <div
                class="flex flex-col gap-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5 dark:border-slate-700 dark:bg-slate-800/70">
                <div class="flex min-w-0 items-start gap-3">
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2">
                            <path d="M12 22a10 10 0 1 0-10-10"/>
                            <path d="m4 12 2.5 2.5L11 10"/>
                        </svg>
                    </span>
                    <div>
                        <div class="text-sm font-extrabold text-slate-900 dark:text-white">Verify account mobile number</div>
                        <p class="mt-1 text-xs font-bold leading-6 text-slate-500 dark:text-slate-400">
                            A one-time code will be sent to
                            <span dir="ltr"
                                  class="inline-block font-extrabold text-slate-700 dark:text-slate-200">{{ $maskedMobile }}</span>
                            the number. Your current password is not needed.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="requestOtp"
                    wire:loading.attr="disabled"
                    wire:target="requestOtp"
                    class="inline-flex h-11 w-full shrink-0 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:hover:bg-brand-400 sm:w-auto"
                >
                    <svg wire:loading.remove wire:target="requestOtp" class="h-5 w-5" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m22 2-7 20-4-9-9-4Z"/>
                        <path d="M22 2 11 13"/>
                    </svg>
                    <span wire:loading.remove wire:target="requestOtp">Send verification code</span>
                    <span wire:loading wire:target="requestOtp">Sending...</span>
                </button>
            </div>
        </div>
    @else
        <form wire:submit.prevent="saveWithOtp" wire:key="otp-password-form"
              class="border-t border-slate-200 p-4 sm:p-5 dark:border-slate-800">
            <div
                class="mb-5 flex flex-col gap-3 rounded-2xl border border-brand-200 bg-brand-50/70 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-brand-500/20 dark:bg-brand-500/10">
                <div class="min-w-0">
                    <div class="text-sm font-extrabold text-slate-900 dark:text-white">Code sent to
                        <bdi>{{ $maskedMobile }}</bdi>
                        successfully
                    </div>
                    <p class="mt-1 text-xs font-bold leading-5 text-slate-500 dark:text-slate-400">
                        Code {{ $otpLength }} digit SMS code and new password.
                    </p>
                </div>

                <div
                    x-data="{
                        now: Math.floor(Date.now() / 1000),
                        resendAt: Number(@js($otpResendAvailableAt)),
                        timer: null,
                        start() {
                            this.stop();
                            this.tick();
                            this.timer = setInterval(() => this.tick(), 1000);
                        },
                        stop() {
                            if (this.timer) clearInterval(this.timer);
                            this.timer = null;
                        },
                        tick() {
                            this.now = Math.floor(Date.now() / 1000);
                        },
                        sync(detail) {
                            this.resendAt = Number(detail.resendAvailableAt ?? 0);
                            this.start();
                        },
                        format(seconds) {
                            const value = Math.max(0, Number(seconds));
                            return Math.floor(value / 60) + ':' + String(value % 60).padStart(2, '0');
                        },
                        get resendSeconds() {
                            return Math.max(0, this.resendAt - this.now);
                        }
                    }"
                    x-init="start()"
                    x-on:account-security-otp-timing-updated.window="sync($event.detail)"
                    class="flex shrink-0 items-center gap-3"
                >
                    <button
                        type="button"
                        wire:click="resendOtp"
                        wire:loading.attr="disabled"
                        wire:target="resendOtp"
                        x-bind:disabled="resendSeconds > 0"
                        class="cursor-pointer text-[11px] font-extrabold text-brand-700 transition hover:text-brand-800 disabled:cursor-not-allowed disabled:text-slate-400 dark:text-brand-300 dark:hover:text-brand-200 dark:disabled:text-slate-600"
                    >
                        <span wire:loading.remove wire:target="resendOtp" x-show="resendSeconds === 0">Resend</span>
                        <span wire:loading.remove wire:target="resendOtp" x-show="resendSeconds > 0">
                            Resend
                            <span dir="ltr" x-text="format(resendSeconds)"></span>
                        </span>
                        <span wire:loading wire:target="resendOtp">Sending...</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="min-w-0">
                    <label for="account-password-otp"
                           class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">Verification code</label>
                    <input
                        id="account-password-otp"
                        type="text"
                        wire:model.defer="otp_code"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        maxlength="{{ $otpLength }}"
                        dir="ltr"
                        placeholder="{{ str_repeat('— ', $otpLength) }}"
                        class="mt-2 w-full rounded-2xl border px-4 py-3 text-center text-base font-extrabold tracking-[0.35em] outline-none transition focus:ring-4
                        @error('otp_code')
                            border-red-300 bg-red-50 text-slate-800 focus:border-red-300 focus:ring-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100 dark:focus:ring-red-500/20
                        @else
                            border-slate-200 bg-slate-50 text-slate-800 placeholder:text-slate-300 focus:border-brand-300 focus:bg-white focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-600 dark:focus:border-brand-500 dark:focus:bg-slate-800 dark:focus:ring-brand-500/20
                        @enderror"
                    >
                    @error('otp_code')
                    <div dir="ltr"
                         class="mt-2 text-xs font-bold leading-5 text-red-600 dark:text-red-300">{{ $message }}</div>
                    @enderror
                </div>

                @include('livewire.front.account.partials.password-field', [
                    'id' => 'account-otp-new-password',
                    'field' => 'password',
                    'label' => 'New password',
                    'placeholder' => 'At least 8 characters',
                ])

                @include('livewire.front.account.partials.password-field', [
                    'id' => 'account-otp-password-confirmation',
                    'field' => 'password_confirmation',
                    'label' => 'Confirm new password',
                    'placeholder' => 'Re-enter your new password',
                ])
            </div>

            @error('resetPassword')
            <div class="mt-3 text-xs font-bold leading-5 text-red-600 dark:text-red-300">{{ $message }}</div>
            @enderror

            <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                <button
                    type="button"
                    wire:click="switchMethod('current_password')"
                    wire:loading.attr="disabled"
                    class="inline-flex h-11 w-full cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 sm:w-auto"
                >
                    Back to current password method
                </button>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="saveWithOtp"
                    class="inline-flex h-11 w-full cursor-pointer items-center justify-center rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:hover:bg-brand-400 sm:w-auto"
                >
                    <span wire:loading.remove wire:target="saveWithOtp">Confirm and change password</span>
                    <span wire:loading wire:target="saveWithOtp">Under review...</span>
                </button>
            </div>
        </form>
    @endif
</section>
