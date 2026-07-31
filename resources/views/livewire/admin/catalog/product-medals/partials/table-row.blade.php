<tr
    wire:key="product-medal-row-{{ $item->id }}"
    wire:loading.class="opacity-60"
    wire:target="startEditing({{ $item->id }})"
    @if($highlightedId === $item->id)
        x-data
    x-init="
            setTimeout(
                () => $wire.set('highlightedId', null),
                2200
            )
        "
    @endif
    :class="{
        'bg-brand-50/40': isSelected({{ $item->id }})
    }"
    @class([
        'group transition duration-700 hover:bg-slate-50/80',
        'bg-brand-50/70 ring-2 ring-inset ring-brand-200' =>
            $highlightedId === $item->id,
        'bg-sky-50/80 ring-2 ring-inset ring-sky-200' =>
            $editingId === $item->id,
    ])
>
    <td class="px-4 py-4 align-top">
        <x-admin.list.bulk-checkbox
            x-bind:checked="isSelected({{ $item->id }})"
            x-on:change="toggleOne({{ $item->id }})"
        />
    </td>

    <x-admin.list.td>
        <div class="flex min-w-[320px] items-start gap-4">
            <div
                class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 p-1.5"
            >
                @if($item->picture?->url)
                    <img
                        src="{{ $item->picture->url }}"
                        alt="{{ $item->title }}"
                        class="h-full w-full object-contain"
                        loading="lazy"
                        decoding="async"
                    >
                @else
                    <svg
                        class="h-7 w-7 text-slate-300"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z"
                        />
                    </svg>
                @endif
            </div>

            <div class="min-w-0 pt-1">
                <button
                    type="button"
                    wire:click="startEditing({{ $item->id }})"
                    wire:loading.class="text-slate-400"
                    wire:target="startEditing({{ $item->id }})"
                    class="block text-right text-sm font-black text-slate-900 transition hover:text-brand-700"
                >
                    {{ $item->title }}
                </button>

                <div class="mt-1 text-[11px] font-bold text-slate-400">
                    شناسه مدال: {{ $item->id }}
                </div>

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
                            <circle
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-opacity=".25"
                                stroke-width="4"
                            ></circle>

                            <path
                                d="M22 12a10 10 0 0 0-10-10"
                                stroke="currentColor"
                                stroke-width="4"
                                stroke-linecap="round"
                            ></path>
                        </svg>

                        <span>ویرایش</span>
                    </x-admin.list.action>

                    <span class="text-slate-300">|</span>

                    <x-admin.list.action
                        tag="button"
                        x-data
                        x-on:click="
                            window.dispatchEvent(
                                new CustomEvent(
                                    'admin-open-confirm',
                                    {
                                        detail: {
                                            title: 'حذف مدال محصول',
                                            text: 'آیا از حذف «{{ $item->title }}» مطمئن هستید؟ اگر این مدال در محصولی استفاده شده باشد، حذف انجام نمی‌شود.',
                                            action: {
                                                type: 'livewire',
                                                event: 'performDeleteProductMedal',
                                                payload: {
                                                    id: {{ $item->id }}
                                                }
                                            }
                                        }
                                    }
                                )
                            )
                        "
                        variant="danger"
                    >
                        حذف
                    </x-admin.list.action>
                </x-admin.list.row-actions>
            </div>
        </div>
    </x-admin.list.td>

    <x-admin.list.td>
        @if($item->is_active)
            <span
                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-extrabold text-emerald-700 ring-1 ring-emerald-100"
            >
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                فعال
            </span>
        @else
            <span
                class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-extrabold text-slate-600 ring-1 ring-slate-200"
            >
                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                غیرفعال
            </span>
        @endif
    </x-admin.list.td>

    <x-admin.list.td>
        <div class="flex items-center gap-2">
            <span
                class="inline-flex h-8 min-w-8 items-center justify-center rounded-full bg-slate-100 px-2 text-xs font-black text-slate-700"
            >
                {{ $item->assignments_count ?? 0 }}
            </span>

            <span class="text-xs font-bold text-slate-500">
                محصول
            </span>
        </div>
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
