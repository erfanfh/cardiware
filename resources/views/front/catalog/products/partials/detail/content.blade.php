<x-front.cart id="product-content" class="border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/90">
    <x-front.cart-title
        title="Overview"
        subtitle="Full product overview and key features"
        iconName="pencil-edit-02"
    />

    <div class="mt-5 tinymce-content dark:text-slate-300">
        {!! $product->body !!}
    </div>

    @include('front.catalog.products.partials.detail.requirements')
</x-front.cart>
