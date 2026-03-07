@props([
   'tabs' => [],
   'active' => null,
])

@php
    $firstTab = collect($tabs)->keys()->first();
    $initialTab = $active ?: $firstTab;
@endphp

<div
    {{ $attributes->merge(['class' => 'space-y-4']) }}
    x-data="{ activeTab: @js($initialTab) }"
>
    <div class="overflow-x-auto">
        <div class="inline-flex min-w-full gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-1">
            @foreach($tabs as $key => $label)
                <button
                    type="button"
                    class="cursor-pointer whitespace-nowrap rounded-xl px-4 py-2 text-sm font-bold transition"
                    :class="activeTab === @js($key)
                        ? 'bg-white text-slate-900 shadow-sm border border-slate-200'
                        : 'text-slate-600 hover:text-slate-800'"
                    @click="activeTab = @js($key)"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div>
        {{ $slot }}
    </div>
</div>
