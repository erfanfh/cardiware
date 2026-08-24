<x-admin.form method="get" action="{{ route('admin.videos.index') }}" class="space-y-5">
    <x-admin.filters.panel :open="$hasActiveFilters">
        <x-admin.filters.header title="مدیریت و جستجوی ویدیوها">
            <x-slot:actions>
                <x-admin.form.button tag="a" href="{{ route('admin.videos.create') }}" variant="solid">
                    + افزودن ویدیوی جدید
                </x-admin.form.button>
            </x-slot:actions>
        </x-admin.filters.header>

        <x-admin.filters.body>
            <x-admin.filters.grid>
                <x-admin.filters.item label="شناسه">
                    <x-admin.form.input name="id" :value="request('id')"/>
                </x-admin.filters.item>
                <x-admin.filters.item label="عنوان">
                    <x-admin.form.input name="title" :value="request('title')"/>
                </x-admin.filters.item>
                <x-admin.filters.item label="نویسنده‌ها">
                    <x-admin.form.async-select name="author_ids" :value="request('author_ids')" :selected-options="$selectedAuthorOptions" :multiple="true" :search-url="route('admin.users.ajax-search')"/>
                </x-admin.filters.item>
                <x-admin.filters.item label="وضعیت">
                    <x-admin.form.select name="status" :options="['' => 'همه وضعیت‌ها'] + $statuses" :value="request('status')"/>
                </x-admin.filters.item>
                <x-admin.filters.item label="منبع">
                    <x-admin.form.select name="source_type" :options="['' => 'همه منابع'] + $sources" :value="request('source_type')"/>
                </x-admin.filters.item>
                <x-admin.filters.item label="دسته‌بندی‌ها">
                    <x-admin.form.async-select name="category_ids" :value="request('category_ids')" :selected-options="$selectedCategoryOptions" :multiple="true" :search-url="route('admin.videos.categories.ajax-search')"/>
                </x-admin.filters.item>
                <x-admin.filters.item label="برچسب‌ها">
                    <x-admin.form.async-select name="tag_ids" :value="request('tag_ids')" :selected-options="$selectedTagOptions" :multiple="true" :search-url="route('admin.videos.tags.ajax-search')"/>
                </x-admin.filters.item>
                <x-admin.filters.item label="تاریخ انتشار">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker name="published_at_from" :value="request('published_at_from')"/>
                        <x-admin.form.date-picker name="published_at_to" :value="request('published_at_to')"/>
                    </div>
                </x-admin.filters.item>
            </x-admin.filters.grid>

            <x-admin.filters.actions>
                <x-admin.form.button type="submit" variant="solid">جستجو</x-admin.form.button>
                @if($hasActiveFilters)
                    <x-admin.form.button tag="a" href="{{ route('admin.videos.index') }}" variant="secondary">حذف فیلترها</x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
