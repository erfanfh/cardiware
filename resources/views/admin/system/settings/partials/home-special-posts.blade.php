<div class="space-y-6">
    <x-admin.metabox title="نوشته های ویژه" :collapsible="false">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 space-y-4">
            <x-admin.field label="عنوان باکس"
            >
                <x-admin.form.input
                    name="special_posts_title"
                    :value="old('special_posts_title', get_setting('home', 'special_posts_title'))"
                />
            </x-admin.field>

            <x-admin.field label="تعداد نوشته نمایشی"
            >
                <x-admin.form.input
                    dir="ltr"
                    min="0"
                    type="number"
                    name="special_posts_posts_counts"
                    :value="old('special_posts_posts_counts', get_setting('home', 'special_posts_posts_counts'))"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>
</div>
