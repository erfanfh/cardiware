@if($product->installationSteps->isNotEmpty())
    <x-front.cart id="installation-steps" class="border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/90">
        <x-front.cart-title
            title="راهنمایی"
            subtitle="راهنمای ساده و مرحله‌ای برای استفاده از محصول"
            iconName="settings-02"
        />

        <div class="mt-5 tinymce-content dark:text-slate-300">
            {!! $product->installationSteps->first()?->body !!}
        </div>
    </x-front.cart>
@endif
