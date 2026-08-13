@php
    $status = $item->status_code;

    $statusClasses = match ($status->value) {
        301, 308 => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        302, 307 => 'bg-sky-50 text-sky-700 ring-sky-100',
        410 => 'bg-rose-50 text-rose-700 ring-rose-100',
        default => 'bg-slate-100 text-slate-700 ring-slate-200',
    };
@endphp

<tr
    wire:key="redirect-row-{{ $item->id }}"
    wire:loading.class="opacity-60"
    wire:target="startEditing({{ $item->id }}), toggleActive({{ $item->id }})"
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
        <div class="min-w-[260px]">
            <button
                type="button"
                wire:click="startEditing({{ $item->id }})"
                wire:loading.attr="disabled"
                wire:target="startEditing({{ $item->id }})"
                class="block max-w-[420px] cursor-pointer break-all text-left font-mono text-xs font-bold leading-6 text-slate-900 transition hover:text-brand-700"
                dir="ltr"
                title="{{ $item->source_path }}"
            >
                {{ $item->source_path }}
            </button>

            <div class="mt-1 text-[11px] font-bold text-slate-400">
                شناسه: {{ $item->id }}
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
                    <span>ویرایش</span>
                </x-admin.list.action>

                <span class="text-slate-300">|</span>

                <x-admin.list.action
                    tag="button"
                    wire:click="toggleActive({{ $item->id }})"
                    wire:loading.attr="disabled"
                    wire:target="toggleActive({{ $item->id }})"
                    variant="primary"
                >
                    {{ $item->is_active ? 'غیرفعال کردن' : 'فعال کردن' }}
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
                                        title: 'حذف ریدایرکت',
                                        text: 'آیا از حذف ریدایرکت «' + @js($item->source_path) + '» مطمئن هستید؟',
                                        action: {
                                            type: 'livewire',
                                            event: 'performDeleteRedirect',
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
    </x-admin.list.td>

    <x-admin.list.td>
        <div class="min-w-[280px] max-w-[440px]">
            @if($status->isGone())
                <div class="flex items-start gap-2 rounded-xl bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700">
                    <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-rose-500"></span>
                    <span>بدون مقصد؛ پاسخ 410 Gone</span>
                </div>
            @else
                <div
                    class="break-all font-mono text-xs leading-6 text-slate-700"
                    dir="ltr"
                    title="{{ $item->target_url }}"
                >
                    {{ $item->target_url }}
                </div>
            @endif
        </div>
    </x-admin.list.td>

    <x-admin.list.td>
        <div class="flex flex-col items-start gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-extrabold ring-1 {{ $statusClasses }}">
                {{ $status->value }}
            </span>

            <span class="text-[11px] font-bold text-slate-500">
                {{ $status->label() }}
            </span>

            @if($item->format !== 'plain')
                <span class="rounded-lg bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-700 ring-1 ring-amber-100">
                    {{ $item->format }}
                </span>
            @endif
        </div>
    </x-admin.list.td>

    <x-admin.list.td>
        @if($item->is_active)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-extrabold text-emerald-700 ring-1 ring-emerald-100">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                فعال
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-extrabold text-slate-600 ring-1 ring-slate-200">
                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                غیرفعال
            </span>
        @endif
    </x-admin.list.td>

    <x-admin.list.td>
        <div class="whitespace-nowrap text-xs font-bold text-slate-500">
            {{ jalali_date($item->created_at, 'Y/n/j') }}
        </div>

        <div class="mt-1 text-[11px] text-slate-400">
            {{ jalali_date($item->created_at, 'H:i') }}
        </div>
    </x-admin.list.td>
</tr>
