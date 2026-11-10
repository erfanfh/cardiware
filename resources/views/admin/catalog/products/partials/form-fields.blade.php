@php
    use App\Application\Seo\Support\SeoTemplateVariableRegistry;
    use App\Domain\Catalog\Enums\ProductStatusEnum;
    use App\Domain\Marketplace\Models\Store;

    $isEdit = isset($product) && $product;
    $quickUrl = $isEdit ? route('products.detail.quick', $product->id) : null;
    $itemUrl = $isEdit
        ? ($product->isPubliclyVisible() ? $product->url : route('preview.products', $product->id))
        : null;
    $itemUrlLabel = $isEdit && $product->isPubliclyVisible() ? 'مشاهده' : 'پیش‌نمایش';

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
        @if($isEdit && filled($quickUrl))
            <x-admin.metabox title="لینک کوتاه">
                <div
                    x-data="{
                    copied: false,
                    timer: null,

                    async copyLink() {
                        const input = this.$refs.shortLink;
                        const value = input.value;

                        try {
                            await navigator.clipboard.writeText(value);
                        } catch (error) {
                            input.focus();
                            input.select();
                            document.execCommand('copy');
                            window.getSelection()?.removeAllRanges();
                        }

                        this.copied = true;

                        clearTimeout(this.timer);

                        this.timer = setTimeout(() => {
                            this.copied = false;
                        }, 1800);
                    }
                }"
                    class="space-y-3"
                >
                    <div
                        class="flex overflow-hidden rounded-xl border border-slate-200 bg-slate-50
                           transition focus-within:border-brand-400 focus-within:ring-4
                           focus-within:ring-brand-500/10"
                    >
                        <input
                            x-ref="shortLink"
                            type="text"
                            readonly
                            dir="ltr"
                            value="{{ $quickUrl }}"
                            class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5
                               text-left text-sm text-slate-700 outline-none ring-0
                               focus:border-0 focus:ring-0"
                            aria-label="لینک کوتاه نوشته"
                        >

                        <button
                            type="button"
                            x-on:click="copyLink"
                            class="flex shrink-0 items-center gap-1.5 border-r border-slate-200
                               bg-white px-3 text-xs font-bold text-slate-600 transition cursor-pointer
                               hover:bg-brand-50 hover:text-brand-700"
                        >
                            <svg
                                x-show="!copied"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-4 w-4"
                            >
                                <rect x="8" y="8" width="11" height="11" rx="2"></rect>
                                <path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"></path>
                            </svg>

                            <svg
                                x-show="copied"
                                x-cloak
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-4 w-4"
                            >
                                <path d="m5 12 4 4L19 6"></path>
                            </svg>

                            <span x-text="copied ? 'کپی شد' : 'کپی'"></span>
                        </button>
                    </div>
                </div>
            </x-admin.metabox>
        @endif

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

        <x-admin.metabox title="تقسیم درآمد محصول">
            <div
                x-data="{ sellerPercent: @js(old('seller_commission_percent', number_format(($product?->seller_commission_basis_points ?? 0) / 100, 2, '.', ''))) }"
                class="space-y-3">
                <x-admin.field label="سهم فروشگاه از فروش (درصد)">
                    <x-admin.form.input
                        name="seller_commission_percent"
                        type="number"
                        min="0"
                        max="100"
                        :value="old('seller_commission_percent', number_format(($product?->seller_commission_basis_points ?? 0) / 100, 2, '.', ''))"
                        x-model="sellerPercent"
                    />
                </x-admin.field>
                <p class="text-sm text-slate-600">سهم پلتفرم: <strong
                        x-text="Number.isFinite(Number(sellerPercent)) ? Math.max(0, 100 - Number(sellerPercent)).toFixed(2) + '٪' : '—'">۱۰۰٪</strong>
                </p>
                <p class="text-xs text-slate-500">سهم فروشگاه و پلتفرم از مبلغ نهایی هر آیتم پس از تخفیف محاسبه می‌شود.
                    تغییر این درصد فقط بر پرداخت‌های بعدی اثر دارد.</p>
            </div>
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
            <div class="flex flex-col gap-4">
                @if($isEdit)
                    <x-admin.field label="افزایش نمایشی بازدید">
                        <x-admin.form.input
                            name="display_views_offset"
                            type="number"
                            min="0"
                            max="4294967295"
                            step="1"
                            :value="old('display_views_offset', $product->display_views_offset ?? 0)"
                        />
                    </x-admin.field>
                @endif

                <x-admin.field
                    class="col-span-12"
                    label="دستور بازکردن تنظیمات بازی (لانچر)"
                    hint="نام فایل اجرایی تنظیمات بازی نسبت به ریشهٔ پوشهٔ نصب — مثال: Settings.exe یا tools/settings.exe. اگر پر شود، لانچر دکمهٔ «تنظیمات بازی» برای این محصول نشان می‌دهد."
                >
                    <x-admin.form.input
                        name="game_settings_command"
                        dir="ltr"
                        placeholder="Settings.exe"
                        :value="old('game_settings_command', $product?->game_settings_command)"
                    />
                </x-admin.field>

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

                <x-admin.field
                    class="col-span-12"
                    label="نمایش میانگین امتیاز"
                >
                    <x-admin.form.select
                        name="display_setting[show_average_rating]"
                        :options="[
                        '0' => 'خیر',
                        '1' => 'بله',
                    ]"
                        :value="old('display_setting.show_average_rating', $product?->displaySetting?->show_average_rating)"
                    />
                </x-admin.field>
            </div>
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
        :itemUrl="$itemUrl"
        :itemUrlLabel="$itemUrlLabel"
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
        title="حجم فایل ها"
        storageKey="product-file-size"
    >
        <x-admin.field>
            <x-admin.form.editor
                name="file_size_body"
                :value="old('file_size_body', $product?->file_size_body)"
                preset="full"
                height="500"
                :toolbar-sticky="false"
            />
        </x-admin.field>
    </x-admin.metabox>

    <x-admin.metabox
        title="گالری ویدیویی"
        storageKey="video-gallery"
    >
        @include('admin.catalog.products.partials.video-gallery')
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
