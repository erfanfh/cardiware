<div x-data>
    <button
        type="button"
        wire:click="toggle"
        wire:loading.attr="disabled"
        wire:target="toggle"
        @class([
            'group/favorite grid h-10 w-10 cursor-pointer place-items-center rounded-2xl border shadow-lg shadow-black/10 backdrop-blur-md transition focus:outline-none focus-visible:ring-4 focus-visible:ring-white/30 disabled:cursor-wait disabled:opacity-70 [&_svg]:h-5 [&_svg]:w-5',
            'border-rose-300/40 bg-rose-500 text-white hover:bg-rose-600 dark:border-rose-400/30 dark:bg-rose-500 dark:hover:bg-rose-400' => $isFavorite,
            'border-white/15 bg-black/25 text-white hover:bg-black/35' => !$isFavorite,
        ])
        aria-label="{{ $isFavorite ? 'Remove from favorites' : 'Add to favorites' }}"
        aria-pressed="{{ $isFavorite ? 'true' : 'false' }}"
        title="{{ $isFavorite ? 'Remove from favorites' : 'Add to favorites' }}"
    >
        <span wire:loading.remove wire:target="toggle" class="transition-transform duration-200 group-hover/favorite:scale-110">
            @include('icons.favourite')
        </span>

        <span wire:loading wire:target="toggle" class="h-4 w-4 animate-spin rounded-full border-2 border-white/35 border-t-white"></span>
    </button>

    <template x-teleport="body">
        <div
            x-data="{
                show: false,
                message: '',
                type: 'success',
                timer: null,
                open(detail) {
                    this.message = detail.message;
                    this.type = detail.type;

                    clearTimeout(this.timer);
                    this.show = false;

                    this.$nextTick(() => {
                        this.show = true;
                        this.timer = setTimeout(() => this.show = false, 4200);
                    });
                }
            }"
            x-on:product-favorite-toast.window="open($event.detail)"
            x-show="show"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-3 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-3 scale-95"
            class="fixed inset-x-3 bottom-4 z-[99999] mx-auto max-w-md sm:inset-x-auto sm:bottom-6 sm:left-6 sm:mx-0"
            style="display: none;"
            role="status"
            aria-live="polite"
        >
            <div
                class="overflow-hidden rounded-2xl border bg-white/95 shadow-2xl shadow-slate-950/15 backdrop-blur-xl dark:bg-slate-900/95 dark:shadow-black/40"
                :class="{
                    'border-emerald-200 dark:border-emerald-500/25': type === 'success',
                    'border-amber-200 dark:border-amber-500/25': type === 'warning',
                    'border-slate-200 dark:border-slate-700': type === 'info',
                }"
            >
                <div class="flex items-start gap-3 p-3.5 sm:p-4" dir="ltr">
                    <div
                        class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl [&_svg]:h-5 [&_svg]:w-5"
                        :class="{
                            'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300': type === 'success',
                            'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300': type === 'warning',
                            'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300': type === 'info',
                        }"
                    >
                        <span x-show="type === 'warning'">
                            @include('icons.login-square-01')
                        </span>

                        <span x-show="type !== 'warning'">
                            @include('icons.favourite')
                        </span>
                    </div>

                    <div class="min-w-0 flex-1 pt-0.5">
                        <div
                            class="text-sm font-black text-slate-900 dark:text-white"
                            x-text="type === 'warning' ? 'Sign in' : 'Favorites list'"
                        ></div>

                        <p
                            class="mt-1 text-xs font-medium leading-6 text-slate-600 dark:text-slate-400"
                            x-text="message"
                        ></p>

                        <a
                            x-show="type === 'warning'"
                            href="{{ front_route('auth.index') }}"
                            class="mt-2.5 inline-flex items-center gap-1 text-xs font-extrabold text-brand-600 transition hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200 [&_svg]:h-3.5 [&_svg]:w-3.5"
                        >
                            Sign in / Register
                            <x-front.arrow-right-02/>
                        </a>
                    </div>

                    <button
                        type="button"
                        x-on:click="show = false"
                        class="grid h-8 w-8 shrink-0 cursor-pointer place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white [&_svg]:h-4 [&_svg]:w-4"
                        aria-label="Dismiss message"
                    >
                        @include('icons.cancel-01')
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
