@props([
    'title' => null,
    'description' => null,
    'count' => null,
    'countLabel' => 'مورد',
])

<div class="flex min-w-0 flex-1 items-start justify-between gap-4">
    <div class="min-w-0">
        @if($title)
            <h2 class="text-base font-bold text-slate-800">
                {{ $title }}
            </h2>
        @endif

        @if($description)
            <p class="mt-1 text-sm text-slate-500">
                {{ $description }}
            </p>
        @endif
    </div>

    @if(!is_null($count))
        <div
            class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-xs font-medium text-slate-600 ring-1 ring-slate-200">
            <span
                class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-white px-1.5 text-slate-800 ring-1 ring-slate-200">
                {{ $count }}
            </span>
            <span>{{ $countLabel }}</span>
        </div>
    @endif
</div>
