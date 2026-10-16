@extends('front.master')

@section('content')
    <main
        @auth
            data-loyalty-time-spent
            data-target-type="product"
            data-target-id="{{ $product->id }}"
        @endauth
        class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8"
        x-data="productTabs(@js($commentsCount ?? 0))"
        x-init="init()"
        :style="`--header-offset: ${headerOffset}px`"
    >
        <x-front.breadcrumbs />
        <section data-smart-sidebar-layout class="grid grid-cols-1 lg:grid-cols-10 gap-6">
            <div data-smart-sidebar-content class="min-w-0 lg:col-span-7 space-y-6">
                @include('front.catalog.products.partials.detail.title')
                @include('front.catalog.products.partials.detail.share-box')
                @include('front.catalog.products.partials.detail.image-gallery')

                @include('front.catalog.products.partials.detail.tabs')

                @include('front.catalog.products.partials.detail.content')
                @include('front.catalog.products.partials.detail.installation-steps')
                @include('front.catalog.products.partials.detail.faqs')
                @include('front.catalog.products.partials.detail.releases')
                @include('front.catalog.products.partials.detail.file-size')
                @include('front.catalog.products.partials.detail.video-gallery')
                @include('front.catalog.products.partials.detail.team-members')
                @include('front.catalog.products.partials.detail.comments')
                @include('front.catalog.products.partials.detail.related-products')
            </div>

            <div data-smart-sidebar-column class="min-w-0 lg:col-span-3">
                <aside data-smart-sidebar-box class="space-y-6">
                    <livewire:front.catalog.product-buy-box :product="$product"/>
                    @include('front.catalog.products.partials.detail.sidebar.medals')
                    @include('front.catalog.products.partials.detail.sidebar.update-progress')
                    @include('front.catalog.products.partials.detail.sidebar.product-information')
                    @include('front.catalog.products.partials.detail.sidebar.store')
                    @include('front.catalog.products.partials.detail.sidebar.tags')
                    {{-- @include('front.catalog.products.partials.detail.sidebar.support') --}}
                </aside>
            </div>
        </section>
    </main>
@endsection
