@props([
    'name',
    'maxWidth' => 'max-w-lg',
])

<div
    x-data="{
        open: false,

        openModal(event) {
            if (event.detail === '{{ $name }}') {
                this.open = true;
                document.documentElement.classList.add('overflow-hidden');
                document.body.classList.add('overflow-hidden');
            }
        },

        closeModal() {
            this.open = false;
            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }"
    x-on:open-modal.window="openModal($event)"
    x-on:close-modal.window="closeModal()"
>
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            x-on:keydown.escape.window="closeModal()"
            class="fixed inset-0 z-[99999] flex items-end sm:items-center justify-center px-3 sm:px-6"
            aria-modal="true"
            role="dialog"
        >
            <div
                x-show="open"
                x-transition.opacity
                x-on:click="closeModal()"
                class="fixed inset-0 bg-slate-950/55 backdrop-blur-sm dark:bg-black/75"
            ></div>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-6 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-6 sm:scale-95"
                class="relative z-[1] w-full {{ $maxWidth }} rounded-t-[2rem] sm:rounded-[2rem] bg-white shadow-2xl ring-1 ring-slate-900/10 overflow-hidden dark:bg-slate-900 dark:ring-slate-700"
            >
                {{ $slot }}
            </div>
        </div>
    </template>
</div>
