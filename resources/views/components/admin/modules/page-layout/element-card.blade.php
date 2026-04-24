@props([
    'elementKey',
    'title',
    'type',
    'typeColor' => 'blue',
    'active' => true,
    'inputName' => null,
    'allowedAreas' => [],
    'settingsUrl' => null,
])

@php
    $typeClasses = match($typeColor) {
        'emerald' => 'bg-emerald-50 text-emerald-700',
        'violet' => 'bg-violet-50 text-violet-700',
        'amber' => 'bg-amber-50 text-amber-700',
        'rose' => 'bg-rose-50 text-rose-700',
        'cyan' => 'bg-cyan-50 text-cyan-700',
        'fuchsia' => 'bg-fuchsia-50 text-fuchsia-700',
        'slate' => 'bg-slate-50 text-slate-700',
        'indigo' => 'bg-indigo-50 text-indigo-700',
        'teal' => 'bg-teal-50 text-teal-700',
        'sky' => 'bg-sky-50 text-sky-700',
        'orange' => 'bg-orange-50 text-orange-700',
        'pink' => 'bg-pink-50 text-pink-700',
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

        <div class="flex shrink-0 items-center gap-2">
            @if($settingsUrl)
                <a href="{{ $settingsUrl }}"
                   class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-400 transition hover:border-slate-300 hover:text-slate-700"
                   title="تنظیمات">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path d="M12 15.5A3.5 3.5 0 1 0 12 8.5A3.5 3.5 0 0 0 12 15.5Z"
                              stroke="currentColor"
                              stroke-width="1.8"/>
                        <path d="M19.4 15
                                 A1.65 1.65 0 0 0 19.73 16.82
                                 L19.79 16.88
                                 A2 2 0 1 1 16.96 19.71
                                 L16.9 19.65
                                 A1.65 1.65 0 0 0 15.08 19.32
                                 A1.65 1.65 0 0 0 14 20.85
                                 V21
                                 A2 2 0 1 1 10 21
                                 V20.91
                                 A1.65 1.65 0 0 0 8.91 19.38
                                 A1.65 1.65 0 0 0 7.09 19.71
                                 L7.03 19.77
                                 A2 2 0 1 1 4.2 16.94
                                 L4.26 16.88
                                 A1.65 1.65 0 0 0 4.59 15.06
                                 A1.65 1.65 0 0 0 3.06 14
                                 H3
                                 A2 2 0 1 1 3 10
                                 H3.09
                                 A1.65 1.65 0 0 0 4.62 8.91
                                 A1.65 1.65 0 0 0 4.29 7.09
                                 L4.23 7.03
                                 A2 2 0 1 1 7.06 4.2
                                 L7.12 4.26
                                 A1.65 1.65 0 0 0 8.94 4.59
                                 H9
                                 A1.65 1.65 0 0 0 10 3.06
                                 V3
                                 A2 2 0 1 1 14 3
                                 V3.09
                                 A1.65 1.65 0 0 0 15.09 4.62
                                 A1.65 1.65 0 0 0 16.91 4.29
                                 L16.97 4.23
                                 A2 2 0 1 1 19.8 7.06
                                 L19.74 7.12
                                 A1.65 1.65 0 0 0 19.41 8.94
                                 V9
                                 A1.65 1.65 0 0 0 20.94 10
                                 H21
                                 A2 2 0 1 1 21 14
                                 H20.91
                                 A1.65 1.65 0 0 0 19.38 15.09Z"
                              stroke="currentColor"
                              stroke-width="1.4"
                              stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>
                </a>
            @endif

            <x-admin.form.switch
                :name="$inputName"
                :checked="$active"
            />
        </div>
    </div>
</div>
