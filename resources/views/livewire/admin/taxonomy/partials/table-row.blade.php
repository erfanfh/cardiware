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
                    tag="a"
                    :href="front_route('posts.tags', $item)"
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
