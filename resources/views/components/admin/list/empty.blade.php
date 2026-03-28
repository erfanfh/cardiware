@props([
    'title' => 'موردی پیدا نشد',
    'description' => 'هنوز داده‌ای برای نمایش وجود ندارد.',
    'actionText' => null,
    'actionHref' => null,
])

<tr>
    <td colspan="{{ $attributes->get('colspan', 100) }}" class="px-6 py-14 text-center">
        <div class="mx-auto max-w-md">
            <div
                class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 ring-1 ring-slate-200">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M4 7h16M4 12h16M4 17h10" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>

            <h3 class="text-base font-bold text-slate-700">{{ $title }}</h3>
            <p class="mt-2 text-sm leading-7 text-slate-500">{{ $description }}</p>

            @if($actionText && $actionHref)
                <div class="mt-5">
                    <a
                        href="{{ $actionHref }}"
                        class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-800"
                    >
                        {{ $actionText }}
                    </a>
                </div>
            @endif
        </div>
    </td>
</tr>
