@php($locale = $locale ?? 'fa')
@php($pricing = collect($data['sections'])->firstWhere('type', 'pricing'))
@php($prefix = $locale === 'en' ? '/en' : '')
{{-- کارت خرید --}}
<div class="rounded-2xl border border-accent/30 bg-surface/80 p-6 shadow-[0_0_60px_-24px_var(--landing-accent)]">
    <h3 class="text-base font-extrabold text-ink">{{ $data['product_name'] }}</h3>
    <p class="mt-1.5 text-xs leading-6 text-muted">
        {{ $locale === 'en' ? 'Full access to this feature and everything else.' : 'دسترسی کامل به این بخش و همه بخش‌های دیگر پچ.' }}
    </p>

    <div class="mt-4 flex items-end gap-2">
        <span class="font-num text-3xl font-black leading-none text-ink">{{ $pricing['plans'][0]['price'] ?? '' }}</span>
        <span class="mb-0.5 text-xs text-muted">{{ $pricing['plans'][0]['period'] ?? '' }}</span>
    </div>

    <a href="{{ $prefix }}/{{ $slug }}#pricing" class="btn-primary mt-5 w-full justify-center !py-3 text-sm">
        {{ $data['buy_label'] ?? ($locale === 'en' ? 'Buy the mod' : 'خرید پچ') }}
    </a>
    <p class="mt-3 text-center text-[11px] text-muted">
        {{ $locale === 'en' ? 'Instant activation · 7-day refund' : 'فعال‌سازی آنی · بازگشت وجه ۷ روزه' }}
    </p>
</div>

{{-- کارت نصب --}}
<div class="rounded-2xl border border-edge/70 bg-surface/60 p-6">
    <h3 class="flex items-center gap-2 text-sm font-extrabold text-ink">
        @include('front.landings.partials.icon', ['name' => 'bolt', 'class' => 'size-4 text-accent'])
        {{ $locale === 'en' ? 'How to install' : 'نصب در ۳ قدم' }}
    </h3>
    <ol class="mt-4 space-y-3">
        @foreach($locale === 'en' ? ['Install MW Launcher', 'Buy & sign in', 'Click Install'] : ['نصب MW Launcher', 'خرید و ورود اکانت', 'کلیک روی نصب'] as $i => $step)
            <li class="flex items-center gap-3 text-xs text-muted">
                <span class="font-num grid size-6 shrink-0 place-items-center rounded-lg bg-accent/15 text-[11px] font-black text-accent">{{ ['۱','۲','۳'][$i] }}</span>
                {{ $step }}
            </li>
        @endforeach
    </ol>
    <a href="{{ $prefix }}/{{ $slug }}#install" class="btn-ghost mt-5 w-full justify-center !py-2.5 text-xs">
        {{ $locale === 'en' ? 'Full guide' : 'راهنمای کامل' }}
    </a>
</div>
