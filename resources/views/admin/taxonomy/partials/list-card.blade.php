<x-admin.metabox>
    <x-slot name="title">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="text-sm font-black text-slate-900">لیست {{ $taxonomyLabelPlural }}</div>
                <div class="mt-1 text-xs text-slate-500">
                    در این بخش می‌توانید {{ $taxonomyLabelPlural }} را جستجو، ویرایش یا حذف کنید.
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative min-w-0 sm:w-[280px]">
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M8.5 3a5.5 5.5 0 1 0 3.472 9.765l3.631 3.632a1 1 0 0 0 1.414-1.414l-3.632-3.631A5.5 5.5 0 0 0 8.5 3ZM5 8.5a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0Z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </span>

                    <x-admin.form.input
                        wire:model.live.debounce.400ms="search"
                        placeholder="جستجو در نام، نامک و توضیح..."
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
    </x-slot>

    <div
        class="mb-4 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex items-center gap-2">
                <x-admin.form.select wire:model="bulkAction" class="min-w-[180px]">
                    <option value="">کار دسته‌جمعی</option>
                    <option value="delete">حذف</option>
                </x-admin.form.select>

                <button
                    type="button"
                    wire:click="applyBulkAction"
                    wire:loading.attr="disabled"
                    wire:target="applyBulkAction"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    اجرا
                </button>
            </div>

            @if(! empty($selectedIds))
                <div
                    class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700">
                    <span>{{ count($selectedIds) }} مورد انتخاب شده</span>

                    <button
                        type="button"
                        wire:click="clearSelection"
                        class="text-brand-700/80 transition hover:text-brand-800"
                    >
                        پاک کردن انتخاب‌ها
                    </button>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-2 text-xs text-slate-500">
            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 font-bold text-slate-700">
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
                                @checked($this->allVisibleSelected ?? false)
                                class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-2 focus:ring-brand-200 focus:ring-offset-0"
                            >
                        </label>
                    </th>

                    <th class="px-4 py-3 text-xs font-black text-slate-700">نام</th>
                    <th class="px-4 py-3 text-xs font-black text-slate-700">نامک</th>
                    <th class="hidden px-4 py-3 text-xs font-black text-slate-700 lg:table-cell">توضیح</th>
                    <th class="px-4 py-3 text-center text-xs font-black text-slate-700">تاریخ</th>
                    <th class="px-4 py-3 text-center text-xs font-black text-slate-700">عملیات</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                @forelse($items as $item)
                    <tr
                        wire:key="taxonomy-row-{{ $item->id }}"
                        class="transition hover:bg-slate-50/80 {{ in_array($item->id, $selectedIds, true) ? 'bg-brand-50/40' : '' }}"
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
                                {{ $item->name }}
                            </button>
                        </td>

                        <td class="px-4 py-4 align-top">
                            <div class="text-sm text-slate-700" dir="ltr">
                                {{ $item->slug ?: '—' }}
                            </div>
                        </td>

                        <td class="hidden px-4 py-4 align-top lg:table-cell">
                            <div class="max-w-[280px] text-sm leading-6 text-slate-500">
                                {{ $item->description ?: '—' }}
                            </div>
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
                            <div class="flex items-center justify-center gap-2">
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
                        <td colspan="6" class="px-6 py-16">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div
                                    class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                                        <path d="M7 7h10M7 12h10M7 17h6" stroke="currentColor" stroke-width="1.8"
                                              stroke-linecap="round"/>
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
                                        عبارت جستجو را تغییر دهید یا آن را پاک کنید تا همه موارد دوباره نمایش داده شوند.
                                    @else
                                        از فرم کنار صفحه برای افزودن اولین {{ $taxonomyLabel }} استفاده کنید.
                                    @endif
                                </div>
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
