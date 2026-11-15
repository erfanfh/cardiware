<div class="space-y-6">
    <x-admin.metabox title="کارت‌های باشگاه (صفحه اصلی)" :collapsible="false">
        <p class="mb-4 text-xs leading-6 text-slate-500">
            این بخش سه کارت دارد: اشتراک ویژه، پروژه‌ها و کاربران برتر. هر کارت را می‌توانید خاموش کنید؛
            چیدمان به‌صورت خودکار با تعداد کارت‌های فعال هماهنگ می‌شود (یک، دو یا سه ستون).
        </p>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <x-admin.field label="نمایش کارت اشتراک ویژه">
                <x-admin.form.switch
                    name="club_cards_show_vip"
                    :checked="old('club_cards_show_vip', get_setting('home', 'club_cards_show_vip', 1))"
                />
            </x-admin.field>

            <x-admin.field label="نمایش کارت پروژه‌ها">
                <x-admin.form.switch
                    name="club_cards_show_projects"
                    :checked="old('club_cards_show_projects', get_setting('home', 'club_cards_show_projects', 1))"
                />
            </x-admin.field>

            <x-admin.field label="نمایش کارت کاربران برتر">
                <x-admin.form.switch
                    name="club_cards_show_leaders"
                    :checked="old('club_cards_show_leaders', get_setting('home', 'club_cards_show_leaders', 1))"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="کارت اشتراک ویژه" :collapsible="false">
        <p class="mb-4 text-xs leading-6 text-slate-500">
            عنوان، توضیحات، تصویر و دکمهٔ این کارت را در
            <a href="{{ route('admin.settings.home', ['tab' => 'subscription']) }}" class="font-bold text-brand-600">تب اشتراک ویژه</a>
            ویرایش کنید.
        </p>
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <x-admin.field label="برچسب کوچک زیر عنوان">
                <x-admin.form.input
                    name="club_cards_vip_badge"
                    value="{{ old('club_cards_vip_badge', get_setting('home', 'club_cards_vip_badge')) }}"
                    placeholder="دسترسی نامحدود به همه پچ‌ها"
                />
            </x-admin.field>

        </div>
    </x-admin.metabox>

    <x-admin.metabox title="کارت پروژه‌ها" :collapsible="false">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <x-admin.field label="زیرعنوان (اختیاری)">
                <x-admin.form.input
                    name="club_cards_projects_subtitle"
                    value="{{ old('club_cards_projects_subtitle', get_setting('home', 'club_cards_projects_subtitle')) }}"
                />
            </x-admin.field>

            <x-admin.field label="حداکثر تعداد پروژه (۱ تا ۲۴)">
                <x-admin.form.input
                    name="club_cards_projects_limit"
                    type="number"
                    min="1"
                    max="24"
                    dir="ltr"
                    value="{{ old('club_cards_projects_limit', get_setting('home', 'club_cards_projects_limit', 8)) }}"
                />
            </x-admin.field>
        </div>

        <p class="mt-4 text-xs text-slate-500">
            عنوان و فهرست پروژه‌ها را در
            <a href="{{ route('admin.settings.home', ['tab' => 'projects']) }}" class="font-bold text-brand-600">تب پروژه‌ها</a>
            ویرایش کنید.
        </p>
    </x-admin.metabox>

    <x-admin.metabox title="کارت کاربران برتر" :collapsible="false">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <x-admin.field label="عنوان">
                <x-admin.form.input
                    name="club_cards_leaders_title"
                    value="{{ old('club_cards_leaders_title', get_setting('home', 'club_cards_leaders_title')) }}"
                    placeholder="کاربران برتر هفته"
                />
            </x-admin.field>

            <x-admin.field label="زیرعنوان">
                <x-admin.form.input
                    name="club_cards_leaders_subtitle"
                    value="{{ old('club_cards_leaders_subtitle', get_setting('home', 'club_cards_leaders_subtitle')) }}"
                    placeholder="فعال‌ترین اعضای باشگاه مخاطبان"
                />
            </x-admin.field>

            <x-admin.field label="حداکثر تعداد کاربر (۱ تا ۵۰)">
                <x-admin.form.input
                    name="club_cards_leaders_limit"
                    type="number"
                    min="1"
                    max="50"
                    dir="ltr"
                    value="{{ old('club_cards_leaders_limit', get_setting('home', 'club_cards_leaders_limit', 12)) }}"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>
</div>
