<div
    wire:key="cart-addon-item-{{ $addonItem->id }}"
    class="group flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-brand-100 hover:bg-brand-50/30 sm:flex-row sm:items-center sm:justify-between"
>
    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2">
            <span
                class="inline-flex items-center gap-1 rounded-xl border border-brand-100 bg-brand-50 px-2 py-1 text-[10px] font-extrabold text-brand-700">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                خدمت افزوده
            </span>

            <span class="line-clamp-1 text-xs font-extrabold text-slate-900">
                {{ $this->itemTitle($addonItem) }}
            </span>
        </div>

        @if($this->itemDescription($addonItem))
            <p class="mt-1 line-clamp-1 text-[11px] leading-5 text-slate-500">
                {{ $this->itemDescription($addonItem) }}
            </p>
        @endif
    </div>

    <div class="flex shrink-0 items-center justify-between gap-2 sm:justify-end">
        <span
            class="rounded-xl bg-slate-100 px-2 py-1 text-xs font-black text-slate-900 transition group-hover:bg-white">
            {{ $this->money($this->itemTotal($addonItem)) }}
        </span>

        <button
            type="button"
            wire:click="removeItem({{ $addonItem->id }})"
            wire:loading.attr="disabled"
            wire:target="removeItem({{ $addonItem->id }})"
            class="inline-flex h-7 w-7 cursor-pointer items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-500 shadow-sm transition hover:border-red-200 hover:bg-red-100 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-60"
            aria-label="حذف خدمت افزوده"
        >
            <svg wire:loading.remove wire:target="removeItem({{ $addonItem->id }})" class="h-3.5 w-3.5"
                 viewBox="0 0 24 24" fill="none">
                <path d="M5 7h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M10 11v5M14 11v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M8 7l.6 12.2A2 2 0 0 0 10.6 21h2.8a2 2 0 0 0 2-1.8L16 7"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M9.5 7V5.8A1.8 1.8 0 0 1 11.3 4h1.4a1.8 1.8 0 0 1 1.8 1.8V7"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>

            <svg wire:loading wire:target="removeItem({{ $addonItem->id }})" class="h-3.5 w-3.5 animate-spin"
                 viewBox="0 0 24 24" fill="none">
                <path d="M12 3a9 9 0 1 1-9 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
    </div>
</div>
