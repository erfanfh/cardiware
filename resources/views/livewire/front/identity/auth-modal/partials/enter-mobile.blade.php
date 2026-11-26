<div>
    @include(
        'livewire.front.identity.auth-modal.partials.shared.step-header',
        [
            'title' => 'Sign in or register',
            'description' => 'Enter your mobile number to determine the next step.',
        ]
    )

    <form
        wire:submit.prevent="startAuthFlow"
        novalidate
        class="space-y-4"
    >
        <div class="min-w-0">
            <label
                for="{{ $name }}-mobile"
                class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300"
            >
                Mobile number
            </label>

            <div class="relative mt-1.5">
                <input
                    id="{{ $name }}-mobile"
                    type="tel"
                    dir="ltr"
                    inputmode="numeric"
                    autocomplete="tel"
                    maxlength="11"
                    wire:model.defer="mobile"
                    placeholder="09123456789"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 pl-10 text-left text-sm font-bold outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:ring-brand-500/20"
                >

                <svg
                    class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M7 2h10a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Z"
                    />

                    <path
                        stroke-linecap="round"
                        d="M10 18h4"
                    />
                </svg>
            </div>

            @include(
                'livewire.front.identity.auth-modal.partials.shared.field-error',
                ['field' => 'mobile']
            )
        </div>

        <p class="text-center text-[10px] font-bold leading-5 text-slate-400 dark:text-slate-500">
            If you already have an account, you will sign in; otherwise registration will begin.
        </p>

        <div class="grid grid-cols-1 gap-2 sm:flex sm:items-center sm:justify-end">
            <button
                type="button"
                x-on:click="closeModal()"
                class="h-11 cursor-pointer rounded-2xl border border-slate-200 bg-white px-4 text-xs font-extrabold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
            >
                Cancel
            </button>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="startAuthFlow"
                class="inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-6 text-xs font-extrabold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:shadow-none"
            >
                <span
                    wire:loading.remove
                    wire:target="startAuthFlow"
                >
                    Continue
                </span>

                <span
                    wire:loading
                    wire:target="startAuthFlow"
                >
                    Under review...
                </span>

                <span
                    wire:loading
                    wire:target="startAuthFlow"
                    class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
                ></span>
            </button>
        </div>
    </form>

    <button
        type="button"
        wire:click="goToEmailLogin"
        wire:loading.attr="disabled"
        class="mt-4 inline-flex h-11 w-full cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-xs font-extrabold text-slate-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
    >
        Sign in with email
    </button>
</div>
