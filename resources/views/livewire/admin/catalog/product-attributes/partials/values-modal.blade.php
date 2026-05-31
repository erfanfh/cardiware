<div
    x-data="{
        open: false,
        loading: false,
        ready: false,
        attributeId: null,
        attributeTitle: '',

        openModal(id, title) {
            this.attributeId = id
            this.attributeTitle = title

            this.ready = false
            this.loading = true
            this.open = true

            this.$nextTick(() => {
                this.$refs.modalPanel?.focus()
            })

            $wire.openValuesModal(id)
                .then(() => {
                    if (this.attributeId !== id || !this.open) {
                        return
                    }

                    this.loading = false
                    this.ready = true

                    this.$nextTick(() => {
                        this.$refs.productAttributeValueTitleInput?.focus()
                    })
                })
        },

        closeModal() {
            this.open = false
            this.loading = false
            this.ready = false
            this.attributeId = null
            this.attributeTitle = ''

            $wire.closeValuesModal()
        }
    }"
    x-cloak
    x-show="open"
    x-transition.opacity.duration.100ms
    x-on:product-attribute-values-modal-open-ui.window="openModal($event.detail.id, $event.detail.title)"
    x-on:keydown.escape.window="if (open) closeModal()"
    class="fixed inset-0 z-[90] flex items-center justify-center px-4 py-6"
>
    <div
        x-show="open"
        x-transition.opacity.duration.100ms
        class="absolute inset-0 bg-slate-900/50 backdrop-blur-[2px]"
        x-on:click="closeModal()"
    ></div>

    <div
        x-show="open"
        x-transition.scale.origin.center.duration.100ms
        x-ref="modalPanel"
        tabindex="-1"
        class="relative z-10 flex max-h-[88vh] w-full max-w-2xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl outline-none"
    >
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
            <div>
                <div class="text-sm font-black text-slate-900">
                    مدیریت مقدارهای ویژگی
                </div>

                <div class="mt-1 text-xs font-bold text-slate-500" x-text="attributeTitle || '—'"></div>
            </div>

            <button
                type="button"
                x-on:click="closeModal()"
                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <div class="overflow-y-auto px-5 py-5">
            <div
                x-show="loading || !ready"
                x-cloak
                class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-10 text-center"
            >
                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-white shadow-sm">
                    <svg class="h-5 w-5 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity=".25"
                                stroke-width="4"></circle>
                        <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4"
                              stroke-linecap="round"></path>
                    </svg>
                </div>

                <div class="mt-3 text-sm font-black text-slate-700">
                    در حال آماده‌سازی مقدارها...
                </div>
            </div>

            <div
                x-show="ready && !loading"
                x-cloak
            >
                <form
                    wire:submit.prevent="saveValue"
                    wire:key="product-attribute-value-form-{{ $editingValueId ? 'edit-' . $editingValueId : 'create' }}"
                    class="mb-5 rounded-2xl border border-slate-200 bg-slate-50/70 p-4"
                >
                    <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
                        <x-admin.field
                            label="عنوان مقدار"
                            for="product-attribute-value-title"
                        >
                            <x-admin.form.input
                                id="product-attribute-value-title"
                                x-ref="productAttributeValueTitleInput"
                                wire:model.defer="valueTitle"
                            />
                        </x-admin.field>

                        <div class="flex items-center gap-2">
                            <x-admin.form.button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="saveValue"
                                size="sm"
                            >
                                <svg
                                    wire:loading
                                    wire:target="saveValue"
                                    class="ml-2 h-4 w-4 animate-spin"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity=".25"
                                            stroke-width="4"></circle>
                                    <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4"
                                          stroke-linecap="round"></path>
                                </svg>

                                <span>
                                    {{ $editingValueId ? 'ذخیره' : 'افزودن' }}
                                </span>
                            </x-admin.form.button>

                            @if($editingValueId)
                                <x-admin.form.button
                                    type="button"
                                    wire:click="cancelEditingValue"
                                    variant="secondary"
                                    size="sm"
                                >
                                    انصراف
                                </x-admin.form.button>
                            @endif
                        </div>
                    </div>

                    @error('valueTitle')
                    <div class="mt-3 text-xs font-bold text-red-600">
                        {{ $message }}
                    </div>
                    @enderror
                </form>

                <div
                    wire:loading.class="opacity-60 pointer-events-none"
                    wire:target="saveValue,startEditingValue,performDeleteAttributeValue"
                    class="transition"
                >
                    @if($values->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($values as $value)
                                <div
                                    wire:key="product-attribute-value-row-{{ $value->id }}"
                                    @if($highlightedValueId === $value->id)
                                        x-data
                                    x-init="setTimeout(() => $wire.set('highlightedValueId', null), 1800)"
                                    @endif
                                    @class([
                                        'flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 transition',
                                        'ring-2 ring-inset ring-brand-200 bg-brand-50/60' => $highlightedValueId === $value->id,
                                        'ring-2 ring-inset ring-sky-200 bg-sky-50/70' => $editingValueId === $value->id,
                                    ])
                                >
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-black text-slate-900">
                                            {{ $value->title }}
                                        </div>

                                        <div class="mt-1 text-[11px] font-bold text-slate-400">
                                            ایجاد شده در {{ jalali_date($value->created_at, 'Y/n/j H:i') }}
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <button
                                            type="button"
                                            wire:click="startEditingValue({{ $value->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="startEditingValue({{ $value->id }})"
                                            class="cursor-pointer inline-flex items-center rounded-xl bg-sky-50 px-3 py-2 text-xs font-black text-sky-700 transition hover:bg-sky-100"
                                        >
                                            ویرایش
                                        </button>

                                        <button
                                            type="button"
                                            x-data
                                            x-on:click="
                                                window.dispatchEvent(new CustomEvent('admin-open-confirm', {
                                                    detail: {
                                                        title: 'حذف مقدار ویژگی',
                                                        text: 'آیا از حذف «{{ $value->title }}» مطمئن هستید؟ اگر این مقدار در محصولات استفاده شده باشد، حذف انجام نمی‌شود.',
                                                        action: {
                                                            type: 'livewire',
                                                            event: 'performDeleteAttributeValue',
                                                            payload: {
                                                                id: {{ $value->id }}
                                                            }
                                                        }
                                                    }
                                                }))
                                            "
                                            class="cursor-pointer inline-flex items-center rounded-xl bg-red-50 px-3 py-2 text-xs font-black text-red-700 transition hover:bg-red-100"
                                        >
                                            حذف
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div
                            class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center">
                            <div class="text-sm font-black text-slate-700">
                                هنوز مقداری برای این ویژگی ثبت نشده است.
                            </div>
                            <div class="mt-2 text-xs leading-6 text-slate-500">
                                از فرم بالا اولین مقدار را اضافه کن.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end border-t border-slate-100 bg-slate-50/80 px-5 py-4">
            <x-admin.form.button
                type="button"
                x-on:click="closeModal()"
                variant="secondary"
                size="sm"
            >
                بستن
            </x-admin.form.button>
        </div>
    </div>
</div>
