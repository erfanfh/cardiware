<x-admin.form method="get" action="{{ route('admin.comments.index') }}" class="space-y-5">
    <x-admin.filters.panel :open="$hasActiveFilters">
        <x-admin.filters.header
            title="مدیریت و جستجوی دیدگاه‌ها"
        />

        <x-admin.filters.body>
            <x-admin.filters.grid>
                <x-admin.filters.item label="شناسه">
                    <x-admin.form.input
                        name="id"
                        :value="request('id')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="نام نویسنده">
                    <x-admin.form.input
                        name="author_name"
                        :value="request('author_name')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="ایمیل نویسنده">
                    <x-admin.form.input
                        name="author_email"
                        :value="request('author_email')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="آی‌پی">
                    <x-admin.form.input
                        name="ip_address"
                        :value="request('ip_address')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="متن دیدگاه">
                    <x-admin.form.input
                        name="content"
                        :value="request('content')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="وضعیت">
                    <x-admin.form.select
                        name="status"
                        :options="['' => 'همه وضعیت‌ها'] + $statuses"
                        :value="request('status')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="نوع دیدگاه">
                    <x-admin.form.select
                        name="type"
                        :options="['' => 'همه دیدگاه‌ها'] + $types"
                        :value="request('type')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="نوع محتوا">
                    <x-admin.form.select
                        name="commentable_type"
                        :options="['' => 'همه محتواها'] + $commentableTypes"
                        :value="request('commentable_type')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="تاریخ ارسال">
                    <div class="flex gap-2">
                        <x-admin.form.date-picker
                            name="created_at_from"
                            :value="request('created_at_from')"
                            placeholder="از تاریخ"
                        />

                        <x-admin.form.date-picker
                            name="created_at_to"
                            :value="request('created_at_to')"
                            placeholder="تا تاریخ"
                        />
                    </div>
                </x-admin.filters.item>

                <x-admin.filters.item label="نوشته مرتبط">
                    <x-admin.form.async-select
                        name="post_id"
                        :value="request('post_id')"
                        :selected-options="$selectedPost"
                        placeholder="جستجو در نوشته‌ها..."
                        :multiple="false"
                        :search-url="route('admin.posts.ajax-search')"
                    />
                </x-admin.filters.item>

                <x-admin.filters.item label="محصول مرتبط">
                    <x-admin.form.async-select
                        name="product_id"
                        :value="request('product_id')"
                        :selected-options="$selectedProduct"
                        placeholder="جستجو در محصولات..."
                        :multiple="false"
                        :search-url="route('admin.products.ajax-search')"
                    />
                </x-admin.filters.item>
            </x-admin.filters.grid>

            <x-admin.filters.actions>
                <x-admin.form.button type="submit" variant="solid">
                    جستجو
                </x-admin.form.button>

                @if($hasActiveFilters)
                    <x-admin.form.button
                        tag="a"
                        href="{{ route('admin.comments.index') }}"
                        variant="secondary"
                    >
                        حذف فیلترها
                    </x-admin.form.button>
                @endif
            </x-admin.filters.actions>
        </x-admin.filters.body>
    </x-admin.filters.panel>
</x-admin.form>
