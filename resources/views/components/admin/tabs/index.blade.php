@props([
   'tabs' => [],
   'active' => null,
   'url' => null
])

@php
    $currentUrlTab = request()->input('tab');
    $initialTab = $active ?: $firstTab;

     if (!$initialTab) {
        $initialTab = collect($tabs)->keys()->first();
    }
     $baseUrl = $url ?: url()->current();
@endphp

<div
    {{ $attributes->merge(['class' => 'space-y-4']) }}
    x-data="{
        activeTab: @js($initialTab),
        tabs: @js($tabs),
        baseUrl: @js($baseUrl)
     }"
    x-init="
        const params = new URLSearchParams(window.location.search);
        const urlTab = params.get('tab');
        if (urlTab && tabs[urlTab]) {
            activeTab = urlTab;
        }
         $watch('activeTab', (value) => {
            const hiddenInput = document.getElementById('active_tab_input');
            if (hiddenInput) {
                hiddenInput.value = value;
            }
        });
    "
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
