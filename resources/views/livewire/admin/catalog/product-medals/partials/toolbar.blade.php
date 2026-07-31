<div
    class="mb-4 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-3 lg:flex-row lg:items-center lg:justify-between"
>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative min-w-0 sm:w-[280px]">
            <span
                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M8.5 3a5.5 5.5 0 1 0 3.472 9.765l3.631 3.632a1 1 0 0 0 1.414-1.414l-3.632-3.631A5.5 5.5 0 0 0 8.5 3ZM5 8.5a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0Z"
                        clip-rule="evenodd"
                    />
                </svg>
            </span>

            <x-admin.form.input
                wire:model.live.debounce.400ms="search"
                placeholder="جستجوی مدال محصول..."
                class="pr-10"
            />
        </div>

        @if($search !== '')
            <x-admin.form.button
                type="button"
                wire:click="$set('search', '')"
                variant="secondary"
                size="sm"
            >
                پاک کردن جستجو
            </x-admin.form.button>
        @endif
    </div>

    <div class="flex items-center gap-2 text-xs text-slate-500">
        <span
            class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 font-bold text-slate-700"
        >
            {{ $items->total() }}
        </span>

        <span>مدال محصول</span>
    </div>
</div>
