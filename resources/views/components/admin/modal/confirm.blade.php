@props([
    'name' => 'confirmModal',
    'title' => 'آیا مطمئن هستی؟',
    'description' => 'این عملیات قابل بازگشت نیست.',
    'confirmText' => 'تأیید',
    'cancelText' => 'انصراف',
])

<div
    x-data="{ open: false, payload: {} }"
    x-on:open-confirm-modal.window="
        if ($event.detail.name !== '{{ $name }}') return;
        payload = $event.detail.payload || {};
        open = true;
    "
    x-on:keydown.escape.window="open = false"
    x-cloak
>
    <div
        x-show="open"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
    >
        <div
            x-show="open"
            x-transition.opacity
            class="absolute inset-0 bg-slate-900/50 backdrop-blur-[1px]"
            x-on:click="open = false"
        ></div>

        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="relative z-10 w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-soft2"
        >
            <div class="mb-4">
                <h3 class="text-base font-bold text-slate-800">{{ $title }}</h3>
                <p class="mt-2 text-sm leading-7 text-slate-500">{{ $description }}</p>
            </div>

            {{ $slot }}

            <div class="mt-6 flex items-center justify-end gap-2">
                <button
                    type="button"
                    x-on:click="open = false"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    {{ $cancelText }}
                </button>

                <button
                    type="button"
                    x-on:click="$dispatch('confirm-modal-accepted', { name: '{{ $name }}', payload }); open = false"
                    class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-rose-700"
                >
                    {{ $confirmText }}
                </button>
            </div>
        </div>
    </div>
</div>
