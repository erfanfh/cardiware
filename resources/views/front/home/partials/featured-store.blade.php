@php
    // Featured store: automatic top seller reward or admin selection; hidden if no active slot.
    // Active slot ID cached for 5 minutes (homepage is the busiest page).
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
        ? app(\App\Application\Marketplace\Actions\GetStoreProductsAction::class)->execute($spotlight->store, 1, 4)
        : collect();
    $spotlightTitle = get_setting('seller_rewards', 'spotlight_title') ?: 'Featured store';

    // Short store description: rich text «About» without tags, truncated.
    $storeAbout = $spotlight
        ? trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($spotlight->store->body ?? ''))))
        : '';
    $storeAbout = \Illuminate\Support\Str::limit($storeAbout, 180);
    $storeUrl = $spotlight && filled($spotlight->store->slug)
        ? route('stores.detail', $spotlight->store->slug)
        : null;
@endphp

@if($spotlight)
    <section id="featured-store"
             class="relative overflow-hidden rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-amber-50/40 p-5 dark:border-emerald-500/20 dark:from-emerald-500/10 dark:via-slate-900 dark:to-slate-900">

        {{-- On desktop: right = store introduction, left = Featured products. --}}
        <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-12">

            <div class="lg:col-span-5">
                <div class="flex items-center gap-3">
                    <img src="{{ $spotlight->store->picture?->url ?: asset('img/user-avatar.png') }}"
                         alt="{{ $spotlight->store->title }}"
                         class="h-14 w-14 shrink-0 rounded-2xl object-cover ring-2 ring-emerald-300"
                         loading="lazy">

                    <div class="min-w-0">
                        <div class="text-[11px] font-extrabold text-emerald-700 dark:text-emerald-300">
                            {{ $spotlight->source === 'reward' ? '🏆 ' . ($spotlight->headline ?: 'Top seller') : '⭐ ' . $spotlightTitle }}
                        </div>
                        <h3 class="truncate text-lg font-black text-slate-900 dark:text-white">
                            {{ $spotlight->store->title }}
                        </h3>
                    </div>
                </div>

                @if($storeAbout !== '')
                    <p class="mt-3 text-xs leading-6 text-slate-600 dark:text-slate-300">
                        {{ $storeAbout }}
                    </p>
                @endif

                @if($storeUrl)
                    <a href="{{ $storeUrl }}"
                       class="mt-4 inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-extrabold text-white shadow-sm shadow-emerald-600/25 transition hover:bg-emerald-700">
                        View store
                        <span class="[&_svg]:h-4 [&_svg]:w-4"><x-front.arrow-right-02/></span>
                    </a>
                @endif
            </div>

            <div class="lg:col-span-7">
                @if($spotlightProducts->isNotEmpty())
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <span class="text-xs font-extrabold text-slate-700 dark:text-slate-200">Featured products</span>

                        @if($storeUrl)
                            <a href="{{ $storeUrl }}"
                               class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800 dark:text-emerald-300">
                                All products
                            </a>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach($spotlightProducts as $product)
                            <x-front.product.card :product="$product"/>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-emerald-200 px-4 py-8 text-center text-xs text-slate-500 dark:border-emerald-500/20 dark:text-slate-400">
                        This store has no active products to display yet.
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
