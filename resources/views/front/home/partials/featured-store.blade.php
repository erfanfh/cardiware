@php
    // فروشگاه ویژه: جایزه‌ی برترین فروشنده (خودکار) یا انتخاب مدیر. بدون جایگاه فعال، چیزی نمایش داده نمی‌شود.
    // شناسه‌ی جایگاه فعال ۵ دقیقه کش می‌شود (صفحه اصلی پربازدیدترین صفحه است).
    $spotlightId = \Illuminate\Support\Facades\Cache::remember('home:featured-store-spotlight', 600, fn () => \App\Domain\Marketplace\Models\StoreSpotlight::query()
        ->active()
        ->whereHas('store', fn ($query) => $query->where('status', \App\Domain\Marketplace\Enums\StoreStatusEnum::ACTIVE->value))
        ->orderByRaw("CASE source WHEN 'admin' THEN 0 ELSE 1 END")
        ->latest('starts_at')
        ->value('id'));
    $spotlight = $spotlightId
        ? \App\Domain\Marketplace\Models\StoreSpotlight::query()->active()->with('store.picture')->find($spotlightId)
        : null;
    $spotlightProducts = $spotlight
        ? app(\App\Application\Marketplace\Actions\GetStoreProductsAction::class)->execute($spotlight->store, 1, 8)
        : collect();
    $spotlightTitle = get_setting('seller_rewards', 'spotlight_title') ?: 'فروشگاه ویژه';
@endphp

@if($spotlight && $spotlightProducts->isNotEmpty())
    <section id="featured-store" class="relative overflow-hidden rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-amber-50/40 p-5 dark:border-emerald-500/20 dark:from-emerald-500/10 dark:via-slate-900 dark:to-slate-900">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex min-w-0 items-center gap-3">
                <img src="{{ $spotlight->store->picture?->url ?: asset('img/user-avatar.png') }}" alt="{{ $spotlight->store->title }}" class="h-12 w-12 shrink-0 rounded-2xl object-cover ring-2 ring-emerald-300">
                <div class="min-w-0">
                    <div class="text-[11px] font-extrabold text-emerald-700 dark:text-emerald-300">
                        {{ $spotlight->source === 'reward' ? '🏆 ' . ($spotlight->headline ?: 'برترین فروشنده') : '⭐ ' . $spotlightTitle }}
                    </div>
                    <h3 class="truncate text-base font-black text-slate-900 dark:text-white">فروشگاه {{ $spotlight->store->title }}</h3>
                </div>
            </div>
            <a href="{{ filled($spotlight->store->slug) ? route('stores.detail', $spotlight->store->slug) : '#' }}" class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 hover:text-emerald-800 dark:text-emerald-300">
                همه محصولات فروشگاه
                <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.arrow-left-02')</span>
            </a>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($spotlightProducts as $product)
                <x-front.product.card :product="$product"/>
            @endforeach
        </div>
    </section>
@endif
