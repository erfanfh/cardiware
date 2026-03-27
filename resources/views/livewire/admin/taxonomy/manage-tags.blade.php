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
                            class="space-y-5"
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

        <div class="relative">
            <div
                wire:loading.class="opacity-60 pointer-events-none"
                wire:target="save"
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
                                    <path fill-rule="evenodd"
                                          d="M8.5 3a5.5 5.5 0 1 0 3.472 9.765l3.631 3.632a1 1 0 0 0 1.414-1.414l-3.632-3.631A5.5 5.5 0 0 0 8.5 3ZM5 8.5a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0Z"
                                          clip-rule="evenodd"/>
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

                        @if(! empty($selectedIds))
                            <div>
                                <x-admin.form.select wire:model="bulkAction" class="min-w-[180px]">
                                    <option value="">کار دسته‌جمعی</option>
                                    <option value="delete">حذف</option>
                                </x-admin.form.select>

                                <button
                                    type="button"
                                    wire:click="applyBulkAction"
                                    wire:loading.attr="disabled"
                                    wire:target="applyBulkAction"
                                    @disabled(empty($selectedIds) || blank($bulkAction))
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    اجرا
                                </button>

                                <div
                                    class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700">
                                    <span>{{ count($selectedIds) }} {{ $taxonomyLabel }} انتخاب شده</span>

                                    <button
                                        type="button"
                                        wire:click="clearSelection"
                                        class="text-brand-700/80 transition hover:text-brand-800"
                                    >
                                        پاک کردن انتخاب‌ها
                                    </button>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span
                        class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 font-bold text-slate-700">
                        {{ $items->total() }}
                    </span>
                            <span>{{ $taxonomyLabel }}</span>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                <tr class="text-right">
                                    <th class="w-14 px-4 py-3">
                                        <label class="inline-flex cursor-pointer items-center justify-center">
                                            <input
                                                type="checkbox"
                                                wire:click="togglePageSelection"
                                                @checked($this->allVisibleSelected)
                                                class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-200 focus:ring-offset-0"
                                            >
                                        </label>
                                    </th>

                                    <th class="px-4 py-3">
                                        <button
                                            type="button"
                                            wire:click="sortByColumn('title')"
                                            class="inline-flex items-center gap-2 text-xs font-black text-slate-700 transition hover:text-slate-900"
                                        >
                                            <span>عنوان</span>

                                            @if($sortBy === 'title')
                                                <span class="text-brand-600">
                                                    {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                                                </span>
                                            @else
                                                <span class="text-slate-300">↕</span>
                                            @endif
                                        </button>
                                    </th>

                                    <th class="hidden px-4 py-3 text-xs font-black text-slate-700 lg:table-cell">
                                        نامک
                                    </th>

                                    <th class="hidden px-4 py-3 text-xs font-black text-slate-700 xl:table-cell">
                                        توضیح
                                    </th>

                                    <th class="px-4 py-3 text-center text-xs font-black text-slate-700">
                                        استفاده
                                    </th>

                                    <th class="px-4 py-3 text-center">
                                        <button
                                            type="button"
                                            wire:click="sortByColumn('created_at')"
                                            class="inline-flex items-center gap-2 text-xs font-black text-slate-700 transition hover:text-slate-900"
                                        >
                                            <span>تاریخ</span>

                                            @if($sortBy === 'created_at')
                                                <span class="text-brand-600">
                                                    {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                                                </span>
                                            @else
                                                <span class="text-slate-300">↕</span>
                                            @endif
                                        </button>
                                    </th>

                                    <th class="px-4 py-3 text-center text-xs font-black text-slate-700">
                                        عملیات
                                    </th>
                                </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">
                                @forelse($items as $item)
                                    @php
                                        $isSelected = in_array($item->id, $selectedIds, true);
                                        $isHighlighted = $highlightedId === $item->id;
                                    @endphp

                                    <tr
                                        wire:key="taxonomy-row-{{ $item->id }}"
                                        @if($highlightedId === $item->id)
                                            x-data x-init="setTimeout(() => $wire.set('highlightedId', null), 2200)"
                                        @endif
                                        @class([
                                            'transition duration-700 hover:bg-slate-50/80',
                                            'bg-brand-50/40' => in_array($item->id, $selectedIds, true),
                                            'bg-brand-50/70 ring-2 ring-inset ring-brand-200' => $highlightedId === $item->id,
                                            'bg-sky-50/80 ring-2 ring-inset ring-sky-200' => $editingId === $item->id,
                                        ])
                                    >
                                        <td class="px-4 py-4 align-top">
                                            <label class="inline-flex cursor-pointer items-center justify-center">
                                                <input
                                                    type="checkbox"
                                                    wire:click="toggleSelection({{ $item->id }})"
                                                    @checked(in_array($item->id, $selectedIds, true))
                                                    class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-200 focus:ring-offset-0"
                                                >
                                            </label>
                                        </td>

                                        <td class="px-4 py-4 align-top">
                                            <button
                                                type="button"
                                                wire:click="startEditing({{ $item->id }})"
                                                class="text-right text-sm font-black text-slate-900 transition hover:text-brand-700"
                                            >
                                                {{ $item->title }}
                                            </button>

                                            <div class="mt-1 text-[11px] text-slate-400 lg:hidden" dir="ltr">
                                                {{ $item->slug ?: '—' }}
                                            </div>
                                        </td>

                                        <td class="hidden px-4 py-4 align-top lg:table-cell">
                                            <div class="text-sm text-slate-700" dir="ltr">
                                                {{ $item->slug ?: '—' }}
                                            </div>
                                        </td>

                                        <td class="hidden px-4 py-4 align-top xl:table-cell">
                                            <div class="max-w-[280px] text-sm leading-6 text-slate-500">
                                                {{ $item->description ?: '—' }}
                                            </div>
                                        </td>

                                        <td class="px-4 py-4 align-top text-center">
                                            <span
                                                class="inline-flex items-center justify-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-700">
                                                {{ $item->usage_count ?? 0 }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-4 align-top text-center">
                                            <div class="text-xs font-bold text-slate-500">
                                                {{ jalali_date($item->created_at, 'Y/n/j') }}
                                            </div>
                                            <div class="mt-1 text-[11px] text-slate-400">
                                                {{ jalali_date($item->created_at, 'H:i') }}
                                            </div>
                                        </td>

                                        <td class="px-4 py-4 align-top text-center">
                                            <div class="flex flex-wrap items-center justify-center gap-2">
                                                <button
                                                    type="button"
                                                    wire:click="startEditing({{ $item->id }})"
                                                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700"
                                                >
                                                    ویرایش
                                                </button>

                                                <button
                                                    type="button"
                                                    wire:click="confirmDelete({{ $item->id }})"
                                                    class="inline-flex items-center justify-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 transition hover:bg-rose-100"
                                                >
                                                    حذف
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-16">
                                            <div class="flex flex-col items-center justify-center text-center">
                                                <div
                                                    class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                                                        <path d="M7 7h10M7 12h10M7 17h6" stroke="currentColor"
                                                              stroke-width="1.8" stroke-linecap="round"/>
                                                        <rect x="3.75" y="3.75" width="16.5" height="16.5" rx="3.25"
                                                              stroke="currentColor" stroke-width="1.5"/>
                                                    </svg>
                                                </div>

                                                <div class="mt-4 text-sm font-black text-slate-900">
                                                    @if($search !== '')
                                                        نتیجه‌ای برای جستجوی شما پیدا نشد
                                                    @else
                                                        هنوز {{ $taxonomyLabel }}ی ثبت نشده است
                                                    @endif
                                                </div>

                                                <div class="mt-2 max-w-md text-sm leading-6 text-slate-500">
                                                    @if($search !== '')
                                                        عبارت جستجو را تغییر دهید یا آن را پاک کنید تا همه موارد دوباره
                                                        نمایش داده شوند.
                                                    @else
                                                        از فرم کنار صفحه برای افزودن اولین {{ $taxonomyLabel }} استفاده
                                                        کنید.
                                                    @endif
                                                </div>

                                                @if($search !== '')
                                                    <button
                                                        type="button"
                                                        wire:click="$set('search', '')"
                                                        class="mt-5 inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                                                    >
                                                        پاک کردن جستجو
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($items->hasPages())
                        <div class="mt-4">
                            {{ $items->links() }}
                        </div>
                    @endif
                </x-admin.metabox>
            </div>

            <div
                wire:loading.flex
                wire:target="save"
                class="absolute inset-0 z-10 hidden items-center justify-center rounded-2xl"
            >
                <div class="absolute inset-0 rounded-2xl bg-white/55 backdrop-blur-[2px]"></div>

                <div
                    class="relative flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-soft">
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

        {{-- Delete Modal --}}
        @if($deletingId)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]" wire:click="cancelDelete"></div>

                <div class="relative w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-soft2">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M12 9v4M12 17h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round"/>
                            </svg>
                        </div>

                        <div class="flex-1">
                            <div class="text-base font-black text-slate-900">
                                حذف {{ $taxonomyLabel }}
                            </div>

                            <div class="mt-2 text-sm leading-6 text-slate-500">
                                آیا از حذف این {{ $taxonomyLabel }} مطمئن هستید؟ این عملیات قابل بازگشت نیست.
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            wire:click="cancelDelete"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                        >
                            انصراف
                        </button>

                        <button
                            type="button"
                            wire:click="deleteTerm"
                            wire:loading.attr="disabled"
                            wire:target="deleteTerm"
                            class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <svg wire:loading wire:target="deleteTerm" class="ml-2 h-4 w-4 animate-spin"
                                 viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity=".25"
                                        stroke-width="4"></circle>
                                <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4"
                                      stroke-linecap="round"></path>
                            </svg>

                            حذف {{ $taxonomyLabel }}
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </x-admin.page>
</div>
