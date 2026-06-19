@if($relatedProducts->isNotEmpty())
    <x-front.cart id="related-products">
        <x-front.cart-title
            title="محصولات مشابه"
            subtitle="موارد مرتبط برای همین سبک محصول"
            iconName="puzzle"
        />

        <div class="mt-4 grid grid-cols-1 gap-3 xs:grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 lg:gap-4">
            @foreach($relatedProducts as $product)
                <x-front.product.card :product="$product"/>
            @endforeach
        </div>
    </x-front.cart>
@endif
