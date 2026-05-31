<section
    x-show="activeTab === 'pricing'"
    x-transition.opacity.duration.200ms
    class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
>
    <div class="mb-4">
        <h3 class="text-sm font-bold text-slate-900">قیمت‌گذاری و فایل‌ها</h3>
        <p class="mt-1 text-xs leading-6 text-slate-500">
            تنظیمات فروش، دانلود و آیتم‌های قابل فروش محصول را مدیریت کنید.
        </p>
    </div>

    @include('admin.catalog.products.partials.information.pricing-physical')

    @include('admin.catalog.products.partials.information.pricing-digital-simple')

    @include('admin.catalog.products.partials.information.pricing-digital-variable')
</section>
