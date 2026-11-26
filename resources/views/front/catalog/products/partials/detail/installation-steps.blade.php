@if($product->installationSteps->isNotEmpty())
    <x-front.cart id="installation-steps" class="border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/90">
        <x-front.cart-title
            title="Help"
            subtitle="Simple step-by-step guide to using the product"
            iconName="settings-02"
        />

        <div class="mt-5 tinymce-content dark:text-slate-300">
            {!! $product->installationSteps->first()?->body !!}
        </div>
    </x-front.cart>
@endif
