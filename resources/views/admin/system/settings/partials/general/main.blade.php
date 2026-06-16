<div class="space-y-6">
    <x-admin.metabox title="اطلاعات پایه سایت">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <x-admin.field
                label="عنوان سایت"
            >
                <x-admin.form.input
                    name="title"
                    value="{{ get_setting('general', 'title') }}"
                />
            </x-admin.field>

            <x-admin.field
                label="توضیح کوتاه"
            >
                <x-admin.form.input
                    name="subtitle"
                    value="{{ get_setting('general', 'subtitle') }}"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="نوشته ها">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <x-admin.field
                label="مبنای نوشته های مرتبط"
                hint="بر اساس این تنظیمات تعیین کنید نوشته های مرتبط براساس چه تنظیماتی نمایش داده شوند."
            >
                <x-admin.form.select
                    name="select_related_content_settings"
                    placeholder="انتخاب تنظیمات"
                    :options="[
                                    'tag' => 'برچسب',
                                    'category' => 'دسته بندی',
                                ]"
                    value="{{ get_setting('general', 'select_related_content_settings') }}"
                />
            </x-admin.field>

            <x-admin.field
                label="تعداد آیتم در صفحه لیست نوشته های دسته بندی و برچسب"
                hint="بر اساس این تنظیمات تعیین کنید چه تعداد نوشته در صفحه نوشته های یک دسته بندی یا برچسب صفحه بندی شوند."
            >
                <x-admin.form.input
                    name="category_tag_posts_count_settings"
                    placeholder="تعداد نوشته"
                    value="{{ get_setting('general', 'category_tag_posts_count_settings') ?? 9 }}"
                />
            </x-admin.field>

            <x-admin.field
                label="تعداد آیتم در صفحه لیست نوشته های نویسندگان"
                hint="بر اساس این تنظیمات تعیین کنید چه تعداد نوشته در صفحه نوشته های یک نویسنده صفحه بندی شوند."
            >
                <x-admin.form.input
                    name="author_posts_count_settings"
                    placeholder="تعداد نوشته"
                    value="{{ get_setting('general', 'author_posts_count_settings') ?? 9 }}"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="نتایج جستجو">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <x-admin.field
                label="تعداد آیتم در صفحه نتایج جستجوی نوشته ها"
                hint="بر اساس این تنظیمات تعیین کنید چه تعداد نوشته در صفحه جستجوی نوشته ها صفحه بندی شوند."
            >
                <x-admin.form.input
                    name="search_posts_count_settings"
                    placeholder="تعداد نوشته"
                    value="{{ get_setting('general', 'search_posts_count_settings') ?? 12 }}"
                />
            </x-admin.field>

            <x-admin.field
                label="تعداد آیتم در صفحه نتایج جستجوی محصولات"
                hint="بر اساس این تنظیمات تعیین کنید چه تعداد محصول در صفحه جستجوی محصولات صفحه بندی شوند."
            >
                <x-admin.form.input
                    name="search_products_count_settings"
                    placeholder="تعداد محصول"
                    value="{{ get_setting('general', 'search_products_count_settings') ?? 12 }}"
                />
            </x-admin.field>

            <x-admin.field
                label="تعداد آیتم در صفحه نتایج جستجوی پروژه ها"
                hint="بر اساس این تنظیمات تعیین کنید چه تعداد پروژه در صفحه جستجوی پروژه ها صفحه بندی شوند."
            >
                <x-admin.form.input
                    name="search_projects_count_settings"
                    placeholder="تعداد پروژه"
                    value="{{ get_setting('general', 'search_projects_count_settings') ?? 12 }}"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>
</div>
