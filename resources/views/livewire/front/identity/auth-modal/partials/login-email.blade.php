<div>
    @include(
        'livewire.front.identity.auth-modal.partials.shared.step-header',
        [
            'title' => 'Sign in with email',
            'description' => 'Enter your account email and password.',
            'backMethod' => 'goToEnterMobile',
        ]
    )

    <form wire:submit.prevent="loginWithEmail" class="space-y-4" novalidate>
        <div>
            <label for="{{ $name }}-login-email" class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">
                Email
            </label>
            <input
                id="{{ $name }}-login-email"
                name="email"
                type="email"
                dir="ltr"
                inputmode="email"
                autocomplete="username"
                maxlength="255"
                wire:model.defer="email"
                class="mt-1.5 w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-left text-sm font-bold outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-500/20"
            >
        </div>

        <div>
            <label for="{{ $name }}-email-password" class="text-[11px] font-extrabold text-slate-600 dark:text-slate-300">
                Password
            </label>
            <input
                id="{{ $name }}-email-password"
                name="password"
                type="password"
                dir="ltr"
                autocomplete="current-password"
                wire:model.defer="password"
                class="mt-1.5 w-full rounded-2xl border border-slate-200 bg-white px-3.5 py-2.5 text-left text-sm font-bold outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-brand-500/20"
            >
        </div>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="loginWithEmail"
            class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-xs font-extrabold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:shadow-none"
        >
            <span wire:loading.remove wire:target="loginWithEmail">Sign in and continue</span>
            <span wire:loading wire:target="loginWithEmail">Signing in...</span>
        </button>
    </form>
</div>
