@php
    $rawPostItems = collect(get_setting('home', 'posts_items') ?? []);

    $categoryIds = $rawPostItems
        ->flatMap(fn ($item) => $item['main_category_ids'] ?? [])
        ->filter()
        ->unique()
        ->values();

    $categoryTitles = \App\Domain\Taxonomy\Models\TaxonomyTerm::query()
        ->where('taxonomy', \App\Domain\Taxonomy\Enums\TaxonomyKeyEnum::POST_CATEGORY)
        ->whereIn('id', $categoryIds)
        ->pluck('title', 'id');

    $postItems = $rawPostItems
        ->map(function ($item) use ($categoryTitles) {
            $mainCategoryIds = collect($item['main_category_ids'] ?? [])
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all();

            return [
                'main_title' => $item['main_title'] ?? '',
                'main_count' => $item['main_count'] ?? 11,
                'hero_count' => $item['hero_count'] ?? 3,
                'list_count' => $item['list_count'] ?? 8,
                'main_category_ids' => $mainCategoryIds,
                'main_categories' => collect($mainCategoryIds)
                    ->map(fn ($id) => [
                        'id' => (string) $id,
                        'title' => $categoryTitles[$id] ?? ('#' . $id),
                    ])
                    ->values()
                    ->all(),
                'sort_order' => $item['sort_order'] ?? 0,
            ];
        })
        ->values()
        ->all();
@endphp

<div class="space-y-6">
    <x-admin.metabox title="نوشته های رایگان" :collapsible="false" class="overflow-visible">
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-bold leading-6 text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
            هر آیتم یک تب است. پست‌های همان دسته (شامل زیردسته‌ها) به ترتیب تاریخ نمایش داده می‌شوند:
            <b>کارت‌های بزرگ</b> با تصویر + <b>لیست جمع‌وجور</b> با تعداد قابل تنظیم.
            پیش‌فرض: ۳ کارت و ۸ ردیف لیست؛ پست‌های پین‌شده جداگانه به کارت‌ها اضافه می‌شوند.
        </div>

        <x-admin.field>
            <x-admin.form.repeater
                :items="old('posts_items', $postItems)"
                name="posts_items"
                title="تب‌ها"
                addText="افزودن تب"
                headingKey="main_title"
                :defaultItem="[
                    'main_title' => '',
                    'main_count' => 11,
                    'hero_count' => 3,
                    'list_count' => 8,
                    'main_category_ids' => [],
                    'main_categories' => [],
                    'sort_order' => 0,
                ]"
            >
                <div class="grid grid-cols-12 gap-4">
                    <x-admin.field class="col-span-4" label="عنوان تب">
                        <x-admin.form.input
                            x-bind:name="field(i, 'main_title')"
                            x-model="it.main_title"
                        />
                    </x-admin.field>

                    <x-admin.field class="col-span-5" label="دسته‌بندی (شامل زیردسته‌ها)">
                        <x-admin.form.async-select
                            x-bind:name="field(i, 'main_category_ids') + '[]'"
                            x-bind:data-initial-options="JSON.stringify(
                                (it.main_categories || []).map(category => ({
                                    value: String(category.id),
                                    label: category.title
                                }))
                            )"
                            x-on:mw-async-select-change="
                                it.main_category_ids = $event.detail.value ?? [];
                                it.main_categories = ($event.detail.items || []).map(item => ({
                                    id: item.value,
                                    title: item.label
                                }))
                            "
                            :multiple="true"
                            :search-url="route('admin.posts.categories.ajax-search')"
                            placeholder="دسته‌های این تب را انتخاب کنید..."
                        />
                    </x-admin.field>

                    <x-admin.field class="col-span-2" label="تعداد کارت بزرگ">
                        <x-admin.form.input
                            x-bind:name="field(i, 'hero_count')"
                            x-model="it.hero_count"
                            type="number"
                            min="3"
                            max="12"
                        />
                    </x-admin.field>

                    <x-admin.field class="col-span-2" label="تعداد لیست">
                        <x-admin.form.input
                            x-bind:name="field(i, 'list_count')"
                            x-model="it.list_count"
                            type="number"
                            min="0"
                            max="30"
                        />
                    </x-admin.field>
                </div>
            </x-admin.form.repeater>
        </x-admin.field>
    </x-admin.metabox>
</div>
