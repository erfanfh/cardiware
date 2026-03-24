@props([
    'action' => null,
    'options' => [],
    'buttonText' => 'اعمال عملیات گروهی',
    'idleText' => 'برای استفاده از کارهای دسته‌جمعی، ابتدا یک یا چند آیتم را انتخاب کن.',
    'selectedText' => 'مورد انتخاب شده',
])

<div
    {{ $attributes->merge([
        'class' => 'rounded-[24px] border p-4 shadow-soft transition-all duration-200'
    ]) }}
    :class="selectedCount > 0
        ? 'border-brand-200 bg-gradient-to-r from-brand-50/80 via-white to-white'
        : 'border-slate-200 bg-white'"
>
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex flex-1 flex-col gap-4 lg:flex-row lg:items-center">
            <div class="flex min-w-0 items-center gap-3">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl transition"
                    :class="selectedCount > 0
                        ? 'bg-brand-100 text-brand-700 ring-1 ring-brand-200'
                        : 'bg-slate-100 text-slate-500 ring-1 ring-slate-200'"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M9 11.5 11 13.5 15.5 9" stroke-width="1.8" stroke-linecap="round"
                              stroke-linejoin="round"/>
                        <path d="M20 12A8 8 0 1 1 4 12a8 8 0 0 1 16 0Z" stroke-width="1.8"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-800">
                            عملیات گروهی
                        </h3>

                        <div
                            x-cloak
                            x-show="selectedCount > 0"
                            class="inline-flex items-center gap-2 rounded-full bg-brand-100 px-2.5 py-1 text-[11px] font-bold text-brand-700 ring-1 ring-brand-200"
                        >
                            <span
                                class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-white px-1 text-[10px] ring-1 ring-brand-200"
                                x-text="selectedCount"
                            ></span>

                            <span>{{ $selectedText }}</span>
                        </div>
                    </div>

                    <p
                        class="mt-1 text-xs leading-6 transition"
                        :class="selectedCount > 0 ? 'text-brand-700/90' : 'text-slate-500'"
                        x-text="selectedCount > 0
                            ? `اکنون می‌توانی عملیات موردنظر را روی ${selectedCount} آیتم انتخاب‌شده اعمال کنی.`
                            : @js($idleText)"
                    ></p>
                </div>
            </div>

            <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                <div class="min-w-[220px] max-w-[280px]">
                    <x-admin.form.select
                        x-model="bulkAction"
                        :options="$options"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <x-admin.button
                        type="button"
                        variant="solid"
                        x-bind:disabled="selectedCount === 0 || !bulkAction"
                        x-on:click="submitBulkAction($refs.bulkActionForm)"
                    >
                        {{ $buttonText }}
                    </x-admin.button>

                    <x-admin.button
                        type="button"
                        variant="ghost"
                        x-cloak
                        x-show="selectedCount > 0"
                        x-on:click="clearSelection()"
                    >
                        لغو انتخاب
                    </x-admin.button>
                </div>
            </div>
        </div>
    </div>

    <form
        x-ref="bulkActionForm"
        method="POST"
        action="{{ $action }}"
        class="hidden"
    >
        @csrf

        <input type="hidden" name="bulk_action" :value="bulkAction">

        <template x-for="id in selected" :key="id">
            <input type="hidden" name="ids[]" :value="id">
        </template>
    </form>
</div>
