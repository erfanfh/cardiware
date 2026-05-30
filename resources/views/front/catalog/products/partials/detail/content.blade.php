<x-front.cart id="product-content">
    <x-front.cart-title
        title="توضیحات کلی"
        subtitle="معرفی کامل محصول و امکانات اصلی"
        iconName="pencil-edit-02"
    />

    <div class="mt-5 tinymce-content">
        {!! $product->body !!}
    </div>

    @include('front.catalog.products.partials.detail.requirements')
</x-front.cart>
