<div
    x-cloak
    x-show="selectedIds.length > 0"
    x-transition.opacity.duration.150ms
    class="mb-4"
>
    <div
        class="flex flex-col gap-3 rounded-2xl border border-brand-200 bg-brand-50/70 p-3 md:flex-row md:items-center md:justify-between"
    >
        <div class="flex items-center gap-2 text-sm font-bold text-brand-800">
            <span
                class="inline-flex h-8 min-w-8 items-center justify-center rounded-full bg-white px-2 text-xs shadow-sm"
            >
                <span x-text="selectedIds.length"></span>
            </span>

            <span>مدال انتخاب شده</span>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <x-admin.form.select
                class="min-w-[190px] bg-white"
                wire:model="bulkAction"
                :options="$bulkActionOptions"
            />

            <x-admin.form.button
                type="button"
                wire:click="applyBulkAction"
                wire:loading.attr="disabled"
                wire:target="applyBulkAction"
                x-bind:disabled="selectedIds.length === 0"
                size="sm"
            >
                اجرا
            </x-admin.form.button>

            <x-admin.form.button
                type="button"
                variant="secondary"
                x-on:click="clearSelections()"
                class="whitespace-nowrap"
                size="sm"
            >
                لغو انتخاب
            </x-admin.form.button>
        </div>
    </div>
</div>
