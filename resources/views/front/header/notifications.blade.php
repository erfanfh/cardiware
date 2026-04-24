@php
    $notifications = get_setting('header', 'notifications');
@endphp
<div
    x-data="{ open: false }"
    class="relative"
    @click.outside="open = false"
>
    <button
        type="button"
        @click="open = !open"
        class="relative cursor-pointer h-10 w-10 [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem] rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center"
        aria-label="اعلان‌ها"
    >
        @include('icons.notification-01')

        @if(count($notifications) > 0)
            <span
                class="absolute -top-1 -left-1 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-brand-500 text-white text-[10px] font-bold leading-none grid place-items-center border-2 border-white"
            >
                {{ count($notifications) > 9 ? '9+' : count($notifications) }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="absolute top-full left-0 z-[100] mt-3 w-[24rem] max-w-[calc(100vw-1rem)] overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl max-sm:right-auto max-sm:left-0 max-sm:mt-2 max-sm:w-[calc(100vw-8rem)] max-sm:max-w-none"
    >
        <div class="border-b border-slate-100 px-4 py-3">
            <div class="text-sm font-extrabold text-slate-900">اعلان‌ها</div>
            <div class="mt-1 text-xs text-slate-500">
                {{ count($notifications) }} اعلان
            </div>
        </div>

        <div class="max-h-[24rem] overflow-y-auto">
            @forelse($notifications as $notification)
                <div
                    class="group flex items-center gap-3 border-b border-slate-100 px-4 py-4 transition last:border-b-0 hover:bg-slate-50">
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-600">
                        @include('icons.notification-01')
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold leading-6 text-slate-700 group-hover:text-slate-900">
                            {{ $notification['title'] }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <div
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-400">
                        @include('icons.notification-01')
                    </div>

                    <div class="mt-4 text-sm font-bold text-slate-700">
                        اعلانی وجود ندارد
                    </div>

                    <p class="mt-1 text-xs leading-6 text-slate-500">
                        فعلاً اعلان جدیدی برای نمایش ندارید.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</div>
