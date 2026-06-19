<x-front.cart id="product-content" class="border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/90">
    <x-front.cart-title
        title="توضیحات کلی"
        subtitle="معرفی کامل محصول و امکانات اصلی"
        iconName="pencil-edit-02"
    />

    <div class="mt-5 tinymce-content dark:text-slate-300">
        {!! $product->body !!}
    </div>

    @include('front.catalog.products.partials.detail.requirements')
</x-front.cart>
