<div class="mb-5 grid grid-cols-1 gap-3 lg:grid-cols-2">
    @foreach($operationalAlerts as $alert)
        @php
            $classes = match($alert['severity']) {
                'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
                default => 'border-red-200 bg-red-50 text-red-800',
            };
        @endphp
        <div class="rounded-2xl border p-4 {{ $classes }}">
            <div class="text-xs font-black">{{ $alert['title'] }}</div>
            <p class="mt-1 text-[11px] leading-6 opacity-80">{{ $alert['description'] }}</p>
        </div>
    @endforeach
</div>
