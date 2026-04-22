<x-admin.list.table>
    <x-admin.list.thead>
        <tr class="text-right">
            <th class="w-14 px-4 py-3">
                <x-admin.list.bulk-checkbox
                    x-bind:checked="allVisibleSelected"
                    x-on:change="toggleVisible()"
                />
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

            <x-admin.list.th>عبارت یکتا</x-admin.list.th>

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
            @php
                $depth = (int) ($item->depth ?? 0);
                $indent = $depth * 1.2;
                $isChild = $depth > 0;
            @endphp

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
                    <x-admin.list.bulk-checkbox
                        x-bind:checked="isSelected({{ $item->id }})"
                        x-on:change="toggleOne({{ $item->id }})"
                    />
                </td>

                <x-admin.list.td>
                    <div class="min-w-[280px]">
                        <div
                            class="relative"
                            style="padding-right: {{ $indent }}rem;"
                        >
                            @if($isChild)
                                <span
                                    class="pointer-events-none absolute right-0 top-2 inline-flex h-6 w-5 items-center justify-center text-slate-300"
                                    aria-hidden="true"
                                >
                                    <span class="block h-px w-4 bg-slate-300"></span>
                                </span>
                            @endif

                            <div class="rounded-xl transition">
                                <button
                                    type="button"
                                    wire:click="startEditing({{ $item->id }})"
                                    wire:loading.class="text-slate-400"
                                    wire:target="startEditing({{ $item->id }})"
                                    class="flex w-full items-start gap-2 rounded-xl px-2 py-2 text-right"
                                >
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="truncate text-sm font-black text-slate-900 transition hover:text-brand-700">
                                                {{ $item->title }}
                                            </span>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <x-admin.list.row-actions class="mt-2">
                            <x-admin.list.action
                                tag="a"
                                :href="route('posts.categories', $item)"
                                target="_blank"
                            >
                                مشاهده
                            </x-admin.list.action>

                            <span class="text-slate-300">|</span>

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
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity=".25"
                                            stroke-width="4"></circle>
                                    <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4"
                                          stroke-linecap="round"></path>
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
                        {{ $item->posts_count }}
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
                title="دسته‌بندی‌ای برای نمایش وجود ندارد"
                description="هنوز هیچ دسته‌بندی‌ای ایجاد نشده یا نتیجه‌ای با فیلترهای فعلی پیدا نشده است."
            />
        @endforelse
    </x-admin.list.tbody>
</x-admin.list.table>

@if($items->hasPages())
    <div class="mt-4">
        {{ $items->links() }}
    </div>
@endif
