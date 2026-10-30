@php($locale = $locale ?? 'fa')
@php($prefix = $locale === 'en' ? '/en' : '')
<nav x-data="{ open: false, scrolled: false, pages: false }" @scroll.window="scrolled = window.scrollY > 24"
     class="fixed inset-x-0 top-0 z-50 transition-all duration-300"
     :class="scrolled || open || pages ? 'bg-page/85 backdrop-blur-xl border-b border-edge/70' : 'bg-transparent border-b border-transparent'">

    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        {{-- لوگو --}}
        @php($navLogo = \App\Support\Landings::themeOverrides($data['theme'] ?? '')['logo'] ?? null)
        <a href="{{ $prefix }}/{{ $slug }}" class="flex items-center gap-3" aria-label="{{ $data['product_name'] }}">
            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-accent to-accent-2 text-lg font-black text-page shadow-[0_0_24px_-4px_var(--landing-accent)]">
                @if($navLogo)<img src="{{ asset('images/landings/logos/emb/'.$navLogo) }}" alt="logo" class="size-8 object-contain">@else M @endif
            </span>
            <span class="leading-tight">
                <span class="block text-base font-extrabold text-ink">{{ $data['product_name'] }}</span>
                <span class="block text-[10px] font-bold tracking-[0.35em] text-muted">{{ $data['brand'] }}</span>
            </span>
        </a>

        {{-- لینک‌های دسکتاپ --}}
        <div class="hidden items-center gap-1 lg:flex">
            @foreach($data['nav'] as $item)
                <a href="{{ $prefix }}/{{ $slug }}{{ $item['href'] }}" class="rounded-lg px-3 py-2 text-sm font-medium text-muted transition-colors hover:bg-surface hover:text-ink">{{ $item['label'] }}</a>
            @endforeach

            {{-- منوی صفحات جزئیات --}}
            @if(!empty($data['pages']))
                <div class="relative" @click.outside="pages = false">
                    <button @click="pages = !pages" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-muted transition-colors hover:bg-surface hover:text-ink">
                        {{ $locale === 'en' ? 'Explore' : 'بیشتر' }}
                        @include('front.landings.partials.icon', ['name' => 'chevron-down', 'class' => 'size-3.5 transition-transform', 'attributes' => ':class="pages ? \'rotate-180\' : \'\'"'])
                    </button>
                    <div x-show="pages" x-collapse x-cloak class="absolute left-0 top-full mt-2 w-56 overflow-hidden rounded-2xl border border-edge bg-surface shadow-2xl">
                        @foreach($data['pages'] as $pageKey => $page)
                            <a href="{{ $prefix }}/{{ $slug }}/{{ $pageKey }}" @click="pages = false"
                               class="flex items-center justify-between gap-2 px-4 py-3 text-sm font-medium text-muted transition-colors hover:bg-surface-2 hover:text-accent">
                                {{ $page['label'] }}
                                @include('front.landings.partials.icon', ['name' => 'arrow', 'class' => 'size-3.5 opacity-50'])
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-2.5">
            {{-- سوییچر زبان --}}
            <a href="{{ \App\Support\Landings::switchUrl($locale === 'en' ? 'fa' : 'en') }}"
               class="grid h-10 min-w-10 place-items-center rounded-lg border border-edge px-2 text-xs font-black tracking-wider text-muted transition-colors hover:border-accent/50 hover:text-accent"
               aria-label="{{ $locale === 'en' ? 'فارسی' : 'English' }}">
                {{ $locale === 'en' ? 'فا' : 'EN' }}
            </a>

            <a href="{{ $prefix }}/{{ $slug }}#pricing" class="btn-primary hidden !px-5 !py-2.5 text-sm sm:inline-flex">
                {{ $locale === 'en' ? 'Buy the mod' : 'خرید پچ' }}
            </a>

            {{-- همبرگر موبایل --}}
            <button @click="open = !open" class="grid size-10 place-items-center rounded-lg border border-edge text-ink lg:hidden" aria-label="menu">
                <span x-show="!open">@include('front.landings.partials.icon', ['name' => 'menu', 'class' => 'size-5'])</span>
                <span x-show="open" x-cloak>@include('front.landings.partials.icon', ['name' => 'x', 'class' => 'size-5'])</span>
            </button>
        </div>
    </div>

    {{-- منوی موبایل --}}
    <div x-show="open" x-collapse x-cloak class="border-t border-edge/60 bg-page/95 backdrop-blur-xl lg:hidden">
        <div class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-4 sm:px-6">
            @foreach($data['nav'] as $item)
                <a href="{{ $prefix }}/{{ $slug }}{{ $item['href'] }}" @click="open = false" class="rounded-lg px-3 py-2.5 text-sm font-medium text-muted transition-colors hover:bg-surface hover:text-ink">{{ $item['label'] }}</a>
            @endforeach
            @foreach(($data['pages'] ?? []) as $pageKey => $page)
                <a href="{{ $prefix }}/{{ $slug }}/{{ $pageKey }}" @click="open = false" class="rounded-lg px-3 py-2.5 text-sm font-medium text-accent/90 transition-colors hover:bg-surface">{{ $page['label'] }}</a>
            @endforeach
            <a href="{{ $prefix }}/{{ $slug }}#pricing" @click="open = false" class="btn-primary mt-2 justify-center text-sm">{{ $locale === 'en' ? 'Buy the mod' : 'خرید پچ' }}</a>
        </div>
    </div>
</nav>
