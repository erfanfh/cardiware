<div class="space-y-6">
    <x-admin.metabox title="ویدیوهای آموزشی" :collapsible="false">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 space-y-4">
            <x-admin.field label="عنوان باکس">
                <x-admin.form.input
                    name="videos_title"
                    :value="old('videos_title', get_setting('home', 'videos_title'))"
                />
            </x-admin.field>

            <x-admin.field label="دسته های مجاز">
                <x-admin.form.async-select
                    name="videos_category_ids"
                    :multiple="true"
                    :value="old('videos_category_ids', get_setting('home', 'videos_category_ids')) ?? []"
                    :selected-options="\App\Domain\Taxonomy\Models\TaxonomyTerm::categories()
                        ->whereIn('id', old('videos_category_ids', get_setting('home', 'videos_category_ids') ?? []))
                        ->get()
                        ->map(fn ($c) => [
                            'value' => $c->id,
                            'label' => $c->title,
                        ])
                        ->all() ?? []"
                    :search-url="route('admin.categories.ajax-search')"
                    placeholder="دسته‌ها را جستجو کنید..."
                    :error="$errors->first('videos_category_ids')"
                />
            </x-admin.field>

            <x-admin.field label="توضیحات باکس" class="col-span-2">
                <x-admin.form.textarea
                    rows="3"
                    name="videos_description"
                    :value="old('videos_description', get_setting('home', 'videos_description'))"
                />
            </x-admin.field>

            <x-admin.field label="متن دکمه باکس">
                <x-admin.form.input
                    name="videos_button_title"
                    :value="old('videos_button_title', get_setting('home', 'videos_button_title'))"
                />
            </x-admin.field>

            <x-admin.field label="لینک دکمه باکس">
                <x-admin.form.input
                    dir="ltr"
                    name="videos_button_url"
                    :value="old('videos_button_url', get_setting('home', 'videos_button_url'))"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>
</div>
