@php
    // As in the previous design, verification badge is active for displayed stores
    $storeVerified = true;
    $storeActive = $store->products_count >= 3;
@endphp

<section class="mw-store-hero {{ $storeVerified ? 'is-verified' : '' }}">
    {{-- Gradient banner with animated layers --}}
    <div class="mw-store-banner" aria-hidden="true">
        <span class="mw-store-banner-stripes"></span>
        <span class="mw-store-banner-glow is-a"></span>
        <span class="mw-store-banner-glow is-b"></span>
        <span class="mw-store-banner-ring"></span>
        <span class="mw-store-banner-dot"></span>
    </div>

    <div class="mw-store-body">
        {{-- Store identity: avatar with rotating ring + Title + Badges --}}
        <div class="mw-store-identity mw-rise d-1">
            <div class="mw-store-avatar">
                <span class="mw-store-avatar-ring" aria-hidden="true"></span>

                <div class="mw-store-avatar-img">
                    <x-front.responsive-image
                        :media="$store->picture"
                        :fallback="$store->picture?->url"
                        :alt="$store->title"
                        :title="$store->title"
                        variant="thumbnail"
                        class="h-full w-full object-cover"
                        wrapper-class="h-full w-full"
                        :skeleton="true"
                        loading="lazy"
                        decoding="async"
                        sizes="112px"
                    />
                </div>

                @if($storeVerified)
                    <span class="mw-store-avatar-badge" aria-hidden="true">
                        @include('icons.checkmark-badge-01')
                    </span>
                @endif
            </div>

            <div class="min-w-0 flex-1 text-center sm:text-start">
                <h1 class="mw-store-title">
                    Store {{ $store->title }}
                </h1>

                <div class="mt-2.5 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                    @foreach(\App\Domain\Marketplace\Models\StoreReward::query()->where('store_id', $store->getKey())->orderByRaw("CASE period_type WHEN 'year' THEN 0 WHEN 'month' THEN 1 ELSE 2 END")->orderBy('rank')->latest('id')->limit(3)->get() as $storeTrophy)
                        <span class="mw-store-chip" title="{{ $storeTrophy->title() }}">{{ $storeTrophy->trophy() }} {{ \App\Domain\Marketplace\Models\StoreReward::PERIOD_LABELS[$storeTrophy->period_type] }} ({{ $storeTrophy->period_label }})</span>
                    @endforeach
                    @if($storeVerified)
                        <span class="mw-store-chip is-verified">
                            <span class="mw-store-chip-dot"></span>
                            Verified seller
                        </span>
                    @endif

                    @if($storeActive)
                        <span class="mw-store-chip is-active">
                            <span class="mw-store-chip-dot"></span>
                            Active seller
                        </span>
                    @endif
                </div>
            </div>
        </div>

        @if($store->body)
            <p class="mw-store-desc mw-rise d-2">
                {!! $store->body !!}
            </p>
        @endif

        <div class="mw-rise d-3">
            <a href="#products" class="mw-store-cta">
                <span class="relative z-10 inline-flex items-center gap-2">
                    View all products
                    <span class="[&_svg]:h-4 [&_svg]:w-4">
                        <x-front.arrow-right-02/>
                    </span>
                </span>
                <span class="mw-store-cta-sheen" aria-hidden="true"></span>
            </a>
        </div>

        {{-- Seller statistics with animated counter --}}
        <div class="mw-store-stats">
            <div class="mw-store-stat mw-rise d-3">
                <span class="mw-store-stat-icon" aria-hidden="true">
                    @include('icons.calendar-03')
                </span>

                <div class="min-w-0">
                    <div class="mw-store-stat-label">Joined on</div>
                    <div class="mw-store-stat-value">
                        {{ (($store->created_at) ? \Illuminate\Support\Carbon::parse($store->created_at)->format('d F Y') : '') }}
                    </div>
                </div>
            </div>

            <div class="mw-store-stat mw-rise d-4">
                <span class="mw-store-stat-icon" aria-hidden="true">
                    @include('icons.shopping-bag-03')
                </span>

                <div class="min-w-0">
                    <div class="mw-store-stat-label">Product count</div>
                    <div class="mw-store-stat-value">
                        <b class="mw-count" data-count-to="{{ (int) $store->products_count }}">0</b>
                    </div>
                </div>
            </div>

            @unless($store->hide_sales_count)
                <div class="mw-store-stat mw-rise d-5">
                    <span class="mw-store-stat-icon" aria-hidden="true">
                        @include('icons.chart-03')
                    </span>

                    <div class="min-w-0">
                        <div class="mw-store-stat-label">Sales count</div>
                        <div class="mw-store-stat-value">
                            <b class="mw-count" data-count-to="{{ (int) $soldQuantity }}">0</b>
                        </div>
                    </div>
                </div>
            @endunless
        </div>
    </div>
</section>
