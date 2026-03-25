@props([
    'colspan' => 1,
    'rowId',
])

<tr
    x-cloak
    x-show="isOpen({{ \Illuminate\Support\Js::from($rowId) }})"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-1"
    class="bg-slate-50/70"
>
    <td colspan="{{ $colspan }}" class="px-4 pb-4 pt-0">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-100">
            {{ $slot }}
        </div>
    </td>
</tr>
