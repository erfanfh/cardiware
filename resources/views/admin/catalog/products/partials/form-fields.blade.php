@php
    use App\Application\Seo\Support\SeoTemplateVariableRegistry;
    use App\Domain\Catalog\Enums\ProductStatusEnum;
    use App\Domain\Marketplace\Models\Store;

    $isEdit = isset($product) && $product;

    $statuses = [];
    foreach (ProductStatusEnum::cases() as $status){
        $statuses[$status->value] = $status->label();
    }

    $defaultStore = $product?->store ?? Store::query()->orderBy('id')->first();

    $storeSelectedOptions = [];

    if ($defaultStore) {
        $storeSelectedOptions[] = [
            'value' => $defaultStore->id,
            'label' => "$defaultStore->title ($defaultStore->id)",
        ];
    }

    $seoVariables = app(SeoTemplateVariableRegistry::class)->forContext('product');
@endphp

<x-admin.page columns="2">
    <x-slot:sidebar>
        <x-admin.metabox title="انتشار">
            <div class="grid grid-cols-12 gap-5">
                <x-admin.field
                    class="col-span-12"
                    label="تاریخ ایجاد"
                >
                    <x-admin.form.date-picker
                        name="published_at"
                        :value="old('published_at', jalali_date($product?->published_at))"
                    />
                </x-admin.field>

                <x-admin.field
                    class="col-span-12"
                    label="وضعیت"
                >
                    <x-admin.form.select
                        name="status"
                        :options="$statuses"
                        :value="old('status', $product?->status?->value)"
                    />
                </x-admin.field>
            </div>
        </x-admin.metabox>

        <x-admin.metabox title="تصویر شاخص">
            <x-admin.form.media-picker
                mode="field"
                name="picture"
                :value="[
                    'id' => old('picture.id', $product?->picture_id),
                    'path' => old('picture.path', $product?->picture?->url),
                ]"
                layout="vertical"
            />
        </x-admin.metabox>

        <x-admin.metabox title="فروشنده" class="overflow-visible">
            <x-admin.form.async-select
                name="store_id"
                placeholder="جستجو و انتخاب فروشنده..."
                :search-url="route('admin.stores.ajax-search')"
                :value="old('store_id', $defaultStore?->id)"
                :selected-options="$storeSelectedOptions"
                :min-chars="1"
                :max-options="20"
                :allow-clear="false"
            />
        </x-admin.metabox>

        <x-admin.metabox title="برچسب‌ها" class="!overflow-visible">
            @php
                $oldExistingIds = collect(old('tags.existing', []))
                    ->filter()
                    ->map(fn ($id) => (int) $id);

                $oldExistingTags = \App\Domain\Taxonomy\Models\TaxonomyTerm::query()
                    ->whereIn('id', $oldExistingIds)
                    ->get(['id', 'title'])
                    ->map(fn ($tag) => [
                        'id' => $tag->id,
                        'name' => $tag->title,
                    ]);

                $oldNewTags = collect(old('tags.new', []))
                    ->filter()
                    ->map(fn ($name) => [
                        'id' => null,
                        'name' => trim((string) $name),
                    ]);

                $resolvedSelectedTags = old('tags.existing') !== null || old('tags.new') !== null
                    ? $oldExistingTags->concat($oldNewTags)->values()->all()
                    : ($selectedTags ?? []);
            @endphp

            <x-admin.form.tags
                existingName="tags[existing]"
                newName="tags[new]"
                :selected="$resolvedSelectedTags"
                :search-url="route('admin.products.tags.ajax-search')"
            />
        </x-admin.metabox>

        <x-admin.metabox title="دسته‌بندی‌ها">
            <x-admin.form.categories
                name="category_ids"
                :items="$allCategories"
                :selected="old('category_ids', $selectedCategoryIds ?? [])"
                :quick-create-url="route('admin.products.categories.quick-create')"
                :search-url="route('admin.products.categories.ajax-search')"
            />
        </x-admin.metabox>

        <x-admin.metabox title="تنظیمات نمایشی">
            <x-admin.field
                class="col-span-12"
                label="نمایش تعداد فروش"
            >
                <x-admin.form.select
                    name="display_setting[show_sales_count]"
                    :options="[
                        '0' => 'خیر',
                        '1' => 'بله',
                    ]"
                    :value="old('display_setting.show_sales_count', $product?->displaySetting?->show_sales_count)"
                />
            </x-admin.field>
        </x-admin.metabox>

        <x-admin.metabox title="پیشرفت آپدیت محصول">
            @include('admin.catalog.products.partials.update-progress')
        </x-admin.metabox>

        <x-admin.metabox title="فیلدهای اطلاعات">
            @include('admin.catalog.products.partials.information-fields')
        </x-admin.metabox>
    </x-slot:sidebar>

    <x-admin.form.validation-errors class="mb-6"/>

    <x-admin.form.wp-title-slug
        titleName="title"
        slugName="slug"
        :titleValue="$product->title ?? ''"
        :slugValue="$product->slug ?? ''"
        basePath="{{ route('home') }}/products/"
        :scope="\App\Domain\Slug\Enums\SlugScopeEnum::PRODUCT"
        :currentId="$product->id ?? null"
        :itemUrl="$product ? route('products.detail', $product) : null"
    />

    <x-admin.metabox title="توضیحات">
        <x-admin.field required>
            <x-admin.form.editor
                name="body"
                :value="old('body', $product?->body)"
                preset="full"
                height="500"
                placeholder="توضیحات محصول ..."
            />
        </x-admin.field>
    </x-admin.metabox>

    <x-admin.metabox title="خلاصه">
        <x-admin.field required
                       hint="این فیلد اختیاری است. درصورتی که تکمیل نشود، خلاصه توضیحات محصول در نظر گرفته خواهد شد.">
            <x-admin.form.textarea
                name="excerpt"
                rows="4"
                placeholder="یک خلاصه کوتاه از محصول ..."
                value="{{ old('excerpt', $product->excerpt ?? '') }}"/>
        </x-admin.field>
    </x-admin.metabox>

    <x-admin.metabox
        title="اطلاعات محصول"
        storageKey="product-information"
    >
        @include('admin.catalog.products.partials.information')
    </x-admin.metabox>

    <x-admin.metabox
        title="سئو"
        storageKey="seo-section"
    >
        @include('admin.catalog.products.partials.seo-box')
    </x-admin.metabox>

    <x-admin.metabox
        title="گالری تصاویر"
        storageKey="image-gallery"
    >
        @include('admin.catalog.products.partials.image-gallery')
    </x-admin.metabox>

    <x-admin.metabox
        title="سیستم مورد نیاز"
        storageKey="requirements"
    >
        @include('admin.catalog.products.partials.requirements')
    </x-admin.metabox>

    <x-admin.metabox
        title="آموزش نصب"
        storageKey="installation-steps"
    >
        @include('admin.catalog.products.partials.installation-steps')
    </x-admin.metabox>

    <x-admin.metabox
        title="سوالات متداول"
        storageKey="faqs"
    >
        @include('admin.catalog.products.partials.faqs')
    </x-admin.metabox>

    <x-admin.metabox
        title="لیست تغییرات"
        storageKey="releases"
    >
        @include('admin.catalog.products.partials.releases')
    </x-admin.metabox>

    <x-admin.metabox
        title="اعضای تیم"
        storageKey="team-members"
        class="overflow-visible"
    >
        @include('admin.catalog.products.partials.team-members')
    </x-admin.metabox>

    <x-admin.metabox
        title="دکمه‌های تکمیلی (بعد از افزودن به سبد خرید)"
        storageKey="product-action-links"
    >
        @include('admin.catalog.products.partials.action-links')
    </x-admin.metabox>

    <x-admin.metabox
        title="مدال‌های محصول"
        storageKey="product-medals"
        class="overflow-visible"
    >
        @include('admin.catalog.products.partials.product-medals')
    </x-admin.metabox>
</x-admin.page>
