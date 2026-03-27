@php
    $taxonomyLabel = 'برچسب';
    $taxonomyLabelPlural = 'برچسب‌ها';
@endphp

<div>
    <x-admin.page
        title="{{ $taxonomyLabelPlural }}"
        description="مدیریت {{ $taxonomyLabelPlural }} سایت بدون رفرش صفحه و با تجربه‌ای سریع‌تر برای ادمین."
    >
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="xl:col-span-4">
                <div class="xl:sticky xl:top-6">
                    @include('admin.taxonomy.partials.form-card', [
                        'taxonomyLabel' => $taxonomyLabel,
                        'taxonomyLabelPlural' => $taxonomyLabelPlural,
                    ])
                </div>
            </div>

            <div class="xl:col-span-8">
                @include('admin.taxonomy.partials.list-card', [
                    'items' => $tags,
                    'taxonomyLabel' => $taxonomyLabel,
                    'taxonomyLabelPlural' => $taxonomyLabelPlural,
                ])
            </div>
        </div>

        @include('admin.taxonomy.partials.delete-modal', [
            'taxonomyLabel' => $taxonomyLabel,
        ])
    </x-admin.page>
</div>
