<div
    x-cloak
    :class="isUpdatingList ? 'flex' : 'hidden'"
    wire:loading.class.remove="hidden"
    wire:loading.class="flex"
    wire:target="save, gotoPage, previousPage, nextPage, search, performDeleteTicketDepartment, sortByColumn, applyBulkAction"
    class="absolute inset-0 z-10 items-center justify-center rounded-2xl"
>
    <div class="absolute inset-0 rounded-2xl bg-white/55 backdrop-blur-[2px]"></div>

    <div class="relative flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-lg">
        <svg class="h-5 w-5 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity=".25" stroke-width="4"></circle>
            <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4"
                  stroke-linecap="round"></path>
        </svg>

        <div class="text-sm font-bold text-slate-700">
            در حال به‌روزرسانی لیست...
        </div>
    </div>
</div>
