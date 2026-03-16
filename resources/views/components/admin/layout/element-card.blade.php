@props([
    'elementKey',
    'title',
    'type',
    'typeColor' => 'blue',
    'active' => true,
    'inputName' => null,
    'allowedAreas' => [],
])

@php
    $typeClasses = match($typeColor) {
        'emerald' => 'bg-emerald-50 text-emerald-700',
        'violet' => 'bg-violet-50 text-violet-700',
        'amber' => 'bg-amber-50 text-amber-700',
        'rose' => 'bg-rose-50 text-rose-700',
        'cyan' => 'bg-cyan-50 text-cyan-700',
        'fuchsia' => 'bg-fuchsia-50 text-fuchsia-700',
        default => 'bg-blue-50 text-blue-700',
    };

    $allowedAreasValue = implode(',', $allowedAreas);
@endphp

<div
    data-layout-element
    data-element-key="{{ $elementKey }}"
    data-allowed-areas="{{ $allowedAreasValue }}"
    class="group rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md"
>
    <div class="flex items-center justify-between gap-4">
        <div class="flex min-w-0 items-center gap-3">
            <button
                type="button"
                data-drag-handle
                class="flex h-8 w-8 cursor-grab items-center justify-center rounded-lg border border-slate-200 text-slate-400 transition hover:border-slate-300 hover:text-slate-600 active:cursor-grabbing"
                title="جابجایی"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <path d="M10 5H14M10 12H14M10 19H14"
                          stroke="currentColor"
                          stroke-width="1.8"
                          stroke-linecap="round"/>
                    <path d="M6 5H6.01M6 12H6.01M6 19H6.01M18 5H18.01M18 12H18.01M18 19H18.01"
                          stroke="currentColor"
                          stroke-width="2.2"
                          stroke-linecap="round"/>
                </svg>
            </button>

            <div class="flex min-w-0 items-center gap-2">
                <h3 class="truncate text-sm font-bold text-slate-800">
                    {{ $title }}
                </h3>

                <span class="rounded-full px-2 py-0.5 text-[11px] font-medium {{ $typeClasses }}">
                    {{ $type }}
                </span>
            </div>
        </div>

        <div class="shrink-0">
            <x-admin.form.switch
                :name="$inputName"
                :checked="$active"
            />
        </div>
    </div>
</div>
