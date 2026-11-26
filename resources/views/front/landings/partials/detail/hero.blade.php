@php($locale = 'en')
@php($prefix = $locale === 'en' ? '/en' : '')
<section class="relative overflow-hidden border-b border-edge/60">
    {{-- Hero background image (optional from config page) --}}
    @if(!empty($pageData['hero_image']))
        <div class="absolute inset-0" aria-hidden="true">
            <img src="{{ asset($pageData['hero_image']) }}" alt="" class="size-full object-cover object-center" loading="eager">
            {{-- Dark overlay for text readability (stronger on text side) --}}
            <div class="absolute inset-0 bg-gradient-to-l from-page via-page/85 to-page/40"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-page via-transparent to-page/60"></div>
        </div>
    @endif
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute -top-40 left-1/2 h-96 w-[60rem] -translate-x-1/2 rounded-full blur-3xl"
             style="background: radial-gradient(closest-side, var(--landing-glow), transparent)"></div>
    </div>

    <div class="relative mx-auto max-w-7xl px-4 pb-12 pt-14 sm:px-6 lg:px-8">
        {{-- breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-muted" aria-label="breadcrumb">
            <a href="{{ $prefix }}/{{ $slug }}" class="transition-colors hover:text-accent">{{ $data['product_name'] }}</a>
            <span class="opacity-50">/</span>
            <span class="font-bold text-accent">{{ $pageData['title'] }}</span>
        </nav>

        <div class="mt-8 max-w-3xl">
            <span class="eyebrow">{{ $pageData['eyebrow'] }}</span>
            <h1 class="mt-4 text-3xl font-black leading-tight text-ink sm:text-5xl">{{ $pageData['title'] }}</h1>
            <p class="mt-5 text-base leading-8 text-muted">{{ $pageData['intro'] }}</p>
        </div>

        {{-- Meta --}}
        <div class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:max-w-3xl">
            @foreach($pageData['meta'] as $meta)
                <div class="rounded-2xl border border-edge/70 bg-surface/60 px-4 py-4 text-center backdrop-blur-sm">
                    <div class="font-num text-2xl font-black text-accent">{{ $meta['value'] }}</div>
                    <div class="mt-1 text-[11px] font-medium text-muted">{{ $meta['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
