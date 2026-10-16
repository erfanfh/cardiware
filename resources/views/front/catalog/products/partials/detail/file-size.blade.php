@if($product->hasFileSizeContent())
    <x-front.cart id="file-size" class="border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/90">
        <x-front.cart-title
            title="حجم فایل ها"
            iconName="file-02"
        />

        <div class="mt-5 tinymce-content dark:text-slate-300">
            {!! $product->file_size_body !!}
        </div>
    </x-front.cart>
@endif
