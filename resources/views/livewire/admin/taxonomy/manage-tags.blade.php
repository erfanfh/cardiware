<div>
    <x-admin.page :columns="2" :sidebarSpan="4" sidebarPosition="right">
        <x-slot:sidebar>
            <div
                wire:key="taxonomy-sidebar-{{ $editingId ? 'edit-' . $editingId : 'create' }}"
                x-data
                x-on:taxonomy-edit-started.window="
                    if (window.innerWidth < 1280) {
                        $el.scrollIntoView({ behavior: 'smooth', block: 'start' })
                    }

                    $nextTick(() => {
                        setTimeout(() => $refs.taxonomyTitleInput?.focus(), 120)
                    })
                "
            >
                <x-admin.metabox :collapsible="false" title="اطلاعات {{ $taxonomyLabel }}">
                    <div class="space-y-5">
                        <form
                            wire:submit.prevent="save"
                            wire:key="taxonomy-form-{{ $editingId ? 'edit-' . $editingId : 'create' }}"
                            wire:loading.class="opacity-70"
                            wire:target="save,cancelEditing"
                            class="space-y-5 transition"
                        >
                            <x-admin.validation-errors/>

                            <div class="space-y-4">
                                <x-admin.field
                                    label="عنوان"
                                    for="taxonomy-term-title"
                                >
                                    <x-admin.form.input
                                        id="taxonomy-term-title"
                                        x-ref="taxonomyTitleInput"
                                        wire:model.live.debounce.400ms="title"
                                    />
                                </x-admin.field>

                                <x-admin.field
                                    label="نامک"
                                    for="taxonomy-term-slug"
                                    hint="اختیاری است. اگر خالی بماند، به‌صورت خودکار ساخته می‌شود."
                                >
                                    <x-admin.form.input
                                        id="taxonomy-term-slug"
                                        class="direction-ltr"
                                        wire:model.live.debounce.400ms="slug"
                                    />
                                </x-admin.field>

                                <x-admin.field
                                    label="توضیحات"
                                    for="taxonomy-term-description"
                                >
                                    <x-admin.form.textarea
                                        id="taxonomy-term-description"
                                        rows="3"
                                        wire:model.live.debounce.400ms="description"
                                    />
                                </x-admin.field>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4">
                                <x-admin.button
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="save"
                                >
                                    <svg
                                        wire:loading
                                        wire:target="save"
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
                                        {{ $this->isEditing ? 'ذخیره تغییرات' : "افزودن {$taxonomyLabel}" }}
                                    </span>
                                </x-admin.button>

                                @if($this->isEditing)
                                    <x-admin.button
                                        type="button"
                                        wire:click="cancelEditing"
                                        variant="secondary"
                                    >
                                        انصراف
                                    </x-admin.button>
                                @endif
                            </div>
                        </form>
                    </div>
                </x-admin.metabox>
            </div>
        </x-slot:sidebar>

        <div
            class="relative"
            x-data="{
                isUpdatingList: false,
                selectedIds: @entangle('selectedIds').live,
                visibleIds: @js($items->pluck('id')->map(fn ($id) => (string) $id)->values()->all()),

                normalizeIds(ids) {
                    return (ids || []).map(id => String(id))
                },

                isSelected(id) {
                    return this.normalizeIds(this.selectedIds).includes(String(id))
                },

                toggleOne(id) {
                    id = String(id)

                    const ids = this.normalizeIds(this.selectedIds)

                    if (ids.includes(id)) {
                        this.selectedIds = ids.filter(item => item !== id)
                        return
                    }

                    this.selectedIds = [...ids, id]
                },

                get allVisibleSelected() {
                    const selected = this.normalizeIds(this.selectedIds)

                    return this.visibleIds.length > 0
                        && this.visibleIds.every(id => selected.includes(String(id)))
                },

                toggleVisible() {
                    const selected = this.normalizeIds(this.selectedIds)

                    if (this.allVisibleSelected) {
                        this.selectedIds = selected.filter(id => !this.visibleIds.includes(String(id)))
                        return
                    }

                    this.selectedIds = [...new Set([...selected, ...this.visibleIds])]
                },

                clearSelections() {
                    this.selectedIds = []
                }
            }"
            x-ref="taxonomyListBox"
            x-on:taxonomy-scroll-to-list.window="
                $refs.taxonomyListBox.scrollIntoView({ behavior: 'smooth', block: 'start' })
            "
            x-on:taxonomy-list-updating-start.window="isUpdatingList = true"
            x-on:taxonomy-list-updating-stop.window="isUpdatingList = false"
        >
            <div
                :class="{ 'opacity-60 pointer-events-none': isUpdatingList }"
                wire:loading.class="opacity-60 pointer-events-none"
                wire:target="createTerm, gotoPage, previousPage, nextPage, search, performDeleteTerm, deleteTerm, sortByColumn"
                class="transition duration-200"
            >
                <x-admin.metabox :collapsible="false" title="لیست {{ $taxonomyLabelPlural }}">
                    <div
                        class="mb-4 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-3 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div class="flex items-center gap-2">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <div class="relative min-w-0 sm:w-[280px]">
                                        <span
                                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M8.5 3a5.5 5.5 0 1 0 3.472 9.765l3.631 3.632a1 1 0 0 0 1.414-1.414l-3.632-3.631A5.5 5.5 0 0 0 8.5 3ZM5 8.5a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0Z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </span>

                                        <x-admin.form.input
                                            wire:model.live.debounce.400ms="search"
                                            placeholder="جستجو ..."
                                            class="pr-10"
                                        />
                                    </div>

                                    @if($search !== '')
                                        <button
                                            type="button"
                                            wire:click="$set('search', '')"
                                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                                        >
                                            پاک کردن جستجو
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <span
                                class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 font-bold text-slate-700">
                                {{ $items->total() }}
                            </span>
                            <span>{{ $taxonomyLabel }}</span>
                        </div>
                    </div>

                    <div
                        x-cloak
                        x-show="selectedIds.length > 0"
                        x-transition.opacity.duration.150ms
                        class="mb-4"
                    >
                        <div
                            class="flex flex-col gap-3 rounded-2xl border border-brand-200 bg-brand-50/70 p-3 md:flex-row md:items-center md:justify-between">
                            <div class="flex items-center gap-2 text-sm font-bold text-brand-800">
                                <span
                                    class="inline-flex h-8 min-w-8 items-center justify-center rounded-full bg-white px-2 text-xs shadow-sm">
                                    <span x-text="selectedIds.length"></span>
                                </span>

                                <span>{{ $taxonomyLabel }} انتخاب شده</span>
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <x-admin.form.select wire:model="bulkAction" class="min-w-[190px] bg-white">
                                    <option value="">کار دسته‌جمعی را انتخاب کنید</option>
                                    <option value="delete">حذف</option>
                                </x-admin.form.select>

                                <x-admin.button
                                    type="button"
                                    wire:click="applyBulkAction"
                                    wire:loading.attr="disabled"
                                    wire:target="applyBulkAction"
                                    x-bind:disabled="selectedIds.length === 0"
                                >
                                    اجرا
                                </x-admin.button>

                                <x-admin.button
                                    type="button"
                                    variant="secondary"
                                    x-on:click="clearSelections()"
                                >
                                    لغو انتخاب
                                </x-admin.button>
                            </div>
                        </div>
                    </div>

                    <x-admin.list.table>
                        <x-admin.list.thead>
                            <tr class="text-right">
                                <th class="w-14 px-4 py-3">
                                    <label class="inline-flex cursor-pointer items-center justify-center">
                                        <input
                                            type="checkbox"
                                            :checked="allVisibleSelected"
                                            x-on:change="toggleVisible()"
                                            class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-200 focus:ring-offset-0"
                                        >
                                    </label>
                                </th>

                                <x-admin.list.th
                                    width="170px"
                                    label="عنوان"
                                    sortable
                                    sortKey="title"
                                    :current-sort="$sortBy"
                                    :current-direction="$sortDirection"
                                    sortMode="livewire"
                                />

                                <x-admin.list.th>نامک</x-admin.list.th>

                                <x-admin.list.th>توضیحات</x-admin.list.th>

                                <x-admin.list.th>استفاده</x-admin.list.th>

                                <x-admin.list.th
                                    label="تاریخ"
                                    sortable
                                    sortKey="created_at"
                                    :current-sort="$sortBy"
                                    :current-direction="$sortDirection"
                                    sortMode="livewire"
                                />
                            </tr>
                        </x-admin.list.thead>

                        <x-admin.list.tbody>
                            @forelse($items as $item)
                                <tr
                                    wire:key="taxonomy-row-{{ $item->id }}"
                                    wire:loading.class="opacity-60"
                                    wire:target="startEditing({{ $item->id }})"
                                    @if($highlightedId === $item->id)
                                        x-data
                                    x-init="setTimeout(() => $wire.set('highlightedId', null), 2200)"
                                    @endif
                                    :class="{ 'bg-brand-50/40': isSelected({{ $item->id }}) }"
                                    @class([
                                        'group transition duration-700 hover:bg-slate-50/80',
                                        'bg-brand-50/70 ring-2 ring-inset ring-brand-200' => $highlightedId === $item->id,
                                        'bg-sky-50/80 ring-2 ring-inset ring-sky-200' => $editingId === $item->id,
                                    ])
                                >
                                    <td class="px-4 py-4 align-top">
                                        <label class="inline-flex cursor-pointer items-center justify-center">
                                            <input
                                                type="checkbox"
                                                :checked="isSelected({{ $item->id }})"
                                                x-on:change="toggleOne({{ $item->id }})"
                                                class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-200 focus:ring-offset-0"
                                            >
                                        </label>
                                    </td>

                                    <x-admin.list.td>
                                        <div class="min-w-[190px]">
                                            <button
                                                type="button"
                                                wire:click="startEditing({{ $item->id }})"
                                                wire:loading.class="text-slate-400"
                                                wire:target="startEditing({{ $item->id }})"
                                                class="text-right text-sm font-black text-slate-900 transition hover:text-brand-700"
                                            >
                                                {{ $item->title }}
                                            </button>

                                            <x-admin.list.row-actions class="mt-3">
                                                <x-admin.list.action
                                                    tag="button"
                                                    wire:click="startEditing({{ $item->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="startEditing({{ $item->id }})"
                                                    class="inline-flex items-center gap-1"
                                                    variant="primary"
                                                >
                                                    <svg
                                                        wire:loading
                                                        wire:target="startEditing({{ $item->id }})"
                                                        class="h-3.5 w-3.5 animate-spin"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                    >
                                                        <circle cx="12" cy="12" r="10" stroke="currentColor"
                                                                stroke-opacity=".25" stroke-width="4"></circle>
                                                        <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor"
                                                              stroke-width="4" stroke-linecap="round"></path>
                                                    </svg>

                                                    <span>ویرایش</span>
                                                </x-admin.list.action>

                                                <span class="text-slate-300">|</span>

                                                <x-admin.list.action
                                                    tag="button"
                                                    x-data
                                                    x-on:click="
                                                        window.dispatchEvent(new CustomEvent('admin-open-confirm', {
                                                            detail: {
                                                                title: 'حذف {{ $taxonomyLabel }}',
                                                                text: 'آیا از حذف «{{ $item->title }}» مطمئن هستید؟ این عملیات قابل بازگشت نیست.',
                                                                action: {
                                                                    type: 'livewire',
                                                                    event: 'performDeleteTerm',
                                                                    payload: {
                                                                        id: {{ $item->id }}
                                                                    }
                                                                }
                                                            }
                                                        }))
                                                    "
                                                    variant="danger"
                                                >
                                                    حذف
                                                </x-admin.list.action>
                                            </x-admin.list.row-actions>
                                        </div>
                                    </x-admin.list.td>

                                    <x-admin.list.td>
                                        <div class="text-sm text-slate-700">
                                            {{ $item->slug ?: '—' }}
                                        </div>
                                    </x-admin.list.td>

                                    <x-admin.list.td>
                                        <div class="max-w-[280px] text-sm leading-6 text-slate-500">
                                            {{ $item->description ?: '—' }}
                                        </div>
                                    </x-admin.list.td>

                                    <x-admin.list.td>
                                        <span
                                            class="inline-flex items-center justify-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-700">
                                            {{ $item->usage_count ?? 0 }}
                                        </span>
                                    </x-admin.list.td>

                                    <x-admin.list.td>
                                        <div class="text-xs font-bold text-slate-500">
                                            {{ jalali_date($item->created_at, 'Y/n/j') }}
                                        </div>
                                        <div class="mt-1 text-[11px] text-slate-400">
                                            {{ jalali_date($item->created_at, 'H:i') }}
                                        </div>
                                    </x-admin.list.td>
                                </tr>
                            @empty
                                <x-admin.list.empty
                                    colspan="7"
                                    title="برچسبی برای نمایش وجود ندارد"
                                    description="هنوز هیچ برچسبی ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
                                />
                            @endforelse
                        </x-admin.list.tbody>
                    </x-admin.list.table>

                    @if($items->hasPages())
                        <div class="mt-4">
                            {{ $items->links() }}
                        </div>
                    @endif
                </x-admin.metabox>
            </div>

            <div
                x-cloak
                :class="isUpdatingList ? 'flex' : 'hidden'"
                wire:loading.class.remove="hidden"
                wire:loading.class="flex"
                wire:target="createTerm, gotoPage, previousPage, nextPage, search, performDeleteTerm, deleteTerm, sortByColumn"
                class="absolute inset-0 z-10 items-center justify-center rounded-2xl"
            >
                <div class="absolute inset-0 rounded-2xl bg-white/55 backdrop-blur-[2px]"></div>

                <div
                    class="relative flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-soft"
                >
                    <svg class="h-5 w-5 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity=".25"
                                stroke-width="4"></circle>
                        <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4"
                              stroke-linecap="round"></path>
                    </svg>

                    <div class="text-sm font-bold text-slate-700">
                        در حال به‌روزرسانی لیست...
                    </div>
                </div>
            </div>
        </div>
    </x-admin.page>
</div>
