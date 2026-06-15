@if($relatedProducts->isNotEmpty())
    <x-front.cart id="related-products">
        <x-front.cart-title
            title="محصولات مشابه"
            subtitle="موارد مرتبط برای همین سبک محصول"
            iconName="puzzle"
        />

        <div class="grid grid-cols-3 gap-4">
            @foreach($relatedProducts as $product)
                <x-front.product.card :product="$product"/>
            @endforeach
        </div>
    </x-front.cart>
@endif
