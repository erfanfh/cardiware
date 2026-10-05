@php
    $vipExpiresAt = auth()->user()?->vipExpiresAt();
    $remainingDays = $vipExpiresAt ? max(1, now()->diffInDays($vipExpiresAt, false)) : 0;
@endphp

<x-front.cart class="overflow-hidden border-slate-200 bg-white/90 dark:border-slate-800 dark:bg-slate-900/90">
    <div class="flex items-center gap-3">
        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-400/10 dark:text-amber-300 [&_svg]:h-5 [&_svg]:w-5">@include('icons.crown-03')</span>
        <div>
            <div class="text-base font-extrabold text-slate-900 dark:text-white">اشتراک VIP</div>
            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $vipExpiresAt ? 'اشتراک شما فعال است' : 'پلن‌های اشتراک ویژه را ببینید' }}</div>
        </div>
    </div>

    @if($vipExpiresAt)
        <div class="mt-5 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
            <div class="text-sm font-black text-emerald-800 dark:text-emerald-200">{{ number_format($remainingDays) }} روز اعتبار باقی‌مانده</div>
            <div class="mt-2 text-xs text-emerald-700 dark:text-emerald-300">معتبر تا {{ jalali_date($vipExpiresAt, 'Y/m/d H:i') }}</div>
        </div>
    @else
        <p class="mt-5 text-sm leading-7 text-slate-600 dark:text-slate-400">پلن مناسب خود را انتخاب کنید و اعتبار VIP را از مسیر امن سبد خرید فعال کنید.</p>
    @endif

    <a href="{{ route('vip.index') }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-amber-500 px-4 py-3 text-sm font-extrabold text-white transition hover:bg-amber-600 dark:hover:bg-amber-400">
        {{ $vipExpiresAt ? 'تمدید اشتراک VIP' : 'مشاهده پلن‌های VIP' }}
        @include('icons.arrow-left-02')
    </a>
</x-front.cart>
