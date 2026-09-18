@php
    $productUpdateProgress = $product?->updateProgress;
    $defaultTitle = \App\Domain\Catalog\Models\ProductUpdateProgress::DEFAULT_TITLE;
    $defaultDescription = \App\Domain\Catalog\Models\ProductUpdateProgress::DEFAULT_DESCRIPTION;
@endphp

<div class="grid grid-cols-12 gap-5">
    <x-admin.field
        class="col-span-12"
        label="نمایش در صفحه محصول"
    >
        <x-admin.form.select
            name="update_progress[is_visible]"
            :options="[
                '0' => 'خیر',
                '1' => 'بله',
            ]"
            :value="old(
                'update_progress.is_visible',
                (int) ($productUpdateProgress?->is_visible ?? false)
            )"
        />
    </x-admin.field>

    <x-admin.field
        class="col-span-12"
        label="عنوان باکس"
        hint="این عنوان در صفحه همین محصول نمایش داده می‌شود."
    >
        <x-admin.form.input
            name="update_progress[title]"
            :value="old(
                'update_progress.title',
                $productUpdateProgress?->title ?: $defaultTitle
            )"
            maxlength="255"
        />
    </x-admin.field>

    <x-admin.field
        class="col-span-12"
        label="توضیحات باکس"
        hint="متن کوتاه زیر عنوان باکس"
    >
        <x-admin.form.textarea
            name="update_progress[description]"
            :value="old(
                'update_progress.description',
                $productUpdateProgress?->description ?: $defaultDescription
            )"
            rows="3"
            maxlength="1000"
        />
    </x-admin.field>

    <x-admin.field
        class="col-span-12"
        label="درصد پیشرفت"
    >
        <x-admin.form.input
            type="number"
            name="update_progress[progress_percentage]"
            :value="old(
                'update_progress.progress_percentage',
                $productUpdateProgress?->progress_percentage ?? 0
            )"
            min="0"
            max="100"
            step="1"
        />
    </x-admin.field>

    <x-admin.field
        class="col-span-12"
        label="تاریخ شروع توسعه"
    >
        <x-admin.form.date-picker
            name="update_progress[started_at]"
            :value="old(
                'update_progress.started_at',
                $productUpdateProgress?->started_at
                    ? jalali_date($productUpdateProgress->started_at)
                    : null
            )"
        />
    </x-admin.field>

    <x-admin.field
        class="col-span-12"
        label="تاریخ انتشار"
    >
        <x-admin.form.date-picker
            name="update_progress[release_at]"
            :value="old(
                'update_progress.release_at',
                $productUpdateProgress?->release_at
                    ? jalali_date($productUpdateProgress->release_at)
                    : null
            )"
        />
    </x-admin.field>
</div>
