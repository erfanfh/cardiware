@props([
    'title' => 'موردی پیدا نشد',
    'description' => 'هنوز داده‌ای برای نمایش وجود ندارد.',
])

<tr>
    <td colspan="{{ $attributes->get('colspan', 100) }}" class="px-6 py-14 text-center">
        <div class="mx-auto max-w-md">
            <div class="mb-3 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M4 7h16M4 12h16M4 17h10" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>

            <h3 class="text-base font-bold text-slate-700">{{ $title }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
        </div>
    </td>
</tr>
