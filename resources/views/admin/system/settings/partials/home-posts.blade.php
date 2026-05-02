@php
    $rawPostItems = collect(get_setting('home', 'posts_items') ?? []);

    $categoryIds = $rawPostItems
        ->flatMap(function ($item) {
            return array_merge(
                $item['main_category_ids'] ?? [],
                $item['list_box_1_category_ids'] ?? [],
                $item['list_box_2_category_ids'] ?? [],
            );
        })
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

            $listBox1CategoryIds = collect($item['list_box_1_category_ids'] ?? [])
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all();

            $listBox2CategoryIds = collect($item['list_box_2_category_ids'] ?? [])
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all();

            return [
                'main_title' => $item['main_title'] ?? '',
                'main_count' => $item['main_count'] ?? 10,
                'main_category_ids' => $mainCategoryIds,
                'main_categories' => collect($mainCategoryIds)
                    ->map(fn ($id) => [
                        'id' => (string) $id,
                        'title' => $categoryTitles[$id] ?? ('#' . $id),
                    ])
                    ->values()
                    ->all(),

                'list_box_1_title' => $item['list_box_1_title'] ?? '',
                'list_box_1_count' => $item['list_box_1_count'] ?? 10,
                'list_box_1_category_ids' => $listBox1CategoryIds,
                'list_box_1_categories' => collect($listBox1CategoryIds)
                    ->map(fn ($id) => [
                        'id' => (string) $id,
                        'title' => $categoryTitles[$id] ?? ('#' . $id),
                    ])
                    ->values()
                    ->all(),

                'list_box_2_title' => $item['list_box_2_title'] ?? '',
                'list_box_2_count' => $item['list_box_2_count'] ?? 10,
                'list_box_2_category_ids' => $listBox2CategoryIds,
                'list_box_2_categories' => collect($listBox2CategoryIds)
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
        <div class="text-xs text-slate-500 mb-4">
            هر آیتم در باکس نوشته های رایگان یک تب می باشد.
        </div>

        <x-admin.field>
            <x-admin.form.repeater
                :items="old('posts_items', $postItems)"
                name="posts_items"
                title="آیتم ها"
                addText="افزودن آیتم"
                headingKey="main_title"
                :defaultItem="[
                    'main_title' => '',
                    'main_count' => 10,
                    'main_category_ids' => [],
                    'main_categories' => [],
                    'list_box_1_title' => '',
                    'list_box_1_count' => 10,
                    'list_box_1_category_ids' => [],
                    'list_box_1_categories' => [],
                    'list_box_2_title' => '',
                    'list_box_2_count' => 10,
                    'list_box_2_category_ids' => [],
                    'list_box_2_categories' => [],
                    'sort_order' => 0,
                ]"
            >
                <div class="grid grid-cols-12 gap-4">
                    <x-admin.field class="col-span-5" label="عنوان اصلی (عنوان تب)">
                        <x-admin.form.input
                            x-bind:name="field(i, 'main_title')"
                            x-model="it.main_title"
                        />
                    </x-admin.field>

                    <x-admin.field class="col-span-5" label="دسته های باکس اصلی">
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
                            placeholder="دسته های اصلی را انتخاب کنید..."
                        />
                    </x-admin.field>

                    <x-admin.field class="col-span-2" label="تعداد نوشته باکس اصلی">
                        <x-admin.form.input
                            x-bind:name="field(i, 'main_count')"
                            x-model="it.main_count"
                            type="number"
                            min="1"
                        />
                    </x-admin.field>

                    <x-admin.field class="col-span-5" label="عنوان باکس لیستی 1">
                        <x-admin.form.input
                            x-bind:name="field(i, 'list_box_1_title')"
                            x-model="it.list_box_1_title"
                        />
                    </x-admin.field>

                    <x-admin.field class="col-span-5" label="دسته بندی های باکس لیستی 1">
                        <x-admin.form.async-select
                            x-bind:name="field(i, 'list_box_1_category_ids') + '[]'"
                            x-bind:data-initial-options="JSON.stringify(
                                (it.list_box_1_categories || []).map(category => ({
                                    value: String(category.id),
                                    label: category.title
                                }))
                            )"
                            x-on:mw-async-select-change="
                                it.list_box_1_category_ids = $event.detail.value ?? [];
                                it.list_box_1_categories = ($event.detail.items || []).map(item => ({
                                    id: item.value,
                                    title: item.label
                                }))
                            "
                            :multiple="true"
                            :search-url="route('admin.posts.categories.ajax-search')"
                            placeholder="دسته بندی های باکس لیستی 1 را انتخاب کنید..."
                        />
                    </x-admin.field>

                    <x-admin.field class="col-span-2" label="تعداد نوشته باکس لیستی 1">
                        <x-admin.form.input
                            x-bind:name="field(i, 'list_box_1_count')"
                            x-model="it.list_box_1_count"
                            type="number"
                            min="1"
                        />
                    </x-admin.field>

                    <x-admin.field class="col-span-5" label="عنوان باکس لیستی 2">
                        <x-admin.form.input
                            x-bind:name="field(i, 'list_box_2_title')"
                            x-model="it.list_box_2_title"
                        />
                    </x-admin.field>

                    <x-admin.field class="col-span-5" label="دسته بندی های باکس لیستی 2">
                        <x-admin.form.async-select
                            x-bind:name="field(i, 'list_box_2_category_ids') + '[]'"
                            x-bind:data-initial-options="JSON.stringify(
                                (it.list_box_2_categories || []).map(category => ({
                                    value: String(category.id),
                                    label: category.title
                                }))
                            )"
                            x-on:mw-async-select-change="
                                it.list_box_2_category_ids = $event.detail.value ?? [];
                                it.list_box_2_categories = ($event.detail.items || []).map(item => ({
                                    id: item.value,
                                    title: item.label
                                }))
                            "
                            :multiple="true"
                            :search-url="route('admin.posts.categories.ajax-search')"
                            placeholder="دسته بندی های باکس لیستی 2 را انتخاب کنید..."
                        />
                    </x-admin.field>

                    <x-admin.field class="col-span-2" label="تعداد نوشته باکس لیستی 2">
                        <x-admin.form.input
                            x-bind:name="field(i, 'list_box_2_count')"
                            x-model="it.list_box_2_count"
                            type="number"
                            min="1"
                        />
                    </x-admin.field>
                </div>
            </x-admin.form.repeater>
        </x-admin.field>
    </x-admin.metabox>
</div>
