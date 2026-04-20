<div class="space-y-6">
    <x-admin.metabox title="تنظیمات اصلی" :collapsible="false">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 space-y-4">
            <x-admin.field label="نام سایت">
                <x-admin.form.input
                    name="site_name"
                    :value="old('site_name', get_setting('seo', 'site_name'))"
                />
            </x-admin.field>

            <x-admin.field label="نام سایت جایگزین">
                <x-admin.form.input
                    name="site_alternate_name"
                    :value="old('site_alternate_name', get_setting('seo', 'site_alternate_name'))"
                />
            </x-admin.field>

            <x-admin.field label="شعار">
                <x-admin.form.input
                    name="site_description"
                    :value="old('site_description', get_setting('seo', 'site_description'))"
                />
            </x-admin.field>

            <x-admin.field label="کاراکتر جدا کننده">
                <x-admin.form.select
                    name="separator"
                    :value="old('separator', get_setting('seo', 'separator'))"
                    :options="[
                        '|' => '|',
                        '-' => '-',
                        ':' => ':',
                        '*' => '*',
                    ]"
                />
            </x-admin.field>

            <x-admin.field
                label="مقدار robots"
                hint="پیشفرض: index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1"
            >
                <x-admin.form.input
                    name="robots"
                    class="direction-ltr"
                    :value="old('robots', get_setting('seo', 'robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'))"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>

    <x-admin.metabox title="شبکه های اجتماعی و تاییدیه ها" :collapsible="false">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 space-y-4">
            <x-admin.field
                label="زبان (Open Graph)"
            >
                <x-admin.form.select
                    name="og_locale"
                    :value="old('og_locale', get_setting('seo', 'og_locale'))"
                    :options="[
                        'fa_IR' => 'فارسی',
                        'en_US' => 'انگلیسی',
                    ]"
                />
            </x-admin.field>

            <x-admin.field
                label="نوع کارت توییتر (Twitter Card)"
            >
                <x-admin.form.select
                    name="twitter_card"
                    :value="old('twitter_card', get_setting('seo', 'twitter_card'))"
                    :options="[
                        'summary_large_image' => 'summary_large_image',
                        'summary' => 'summary',
                    ]"
                />
            </x-admin.field>

            <x-admin.field label="کد تایید گوگل">
                <x-admin.form.input
                    name="google_site_verification"
                    class="direction-ltr"
                    :value="old('google_site_verification', get_setting('seo', 'google_site_verification'))"
                />
            </x-admin.field>

            <x-admin.field label="کد تایید بینگ">
                <x-admin.form.input
                    name="bing_site_verification"
                    class="direction-ltr"
                    :value="old('bing_site_verification', get_setting('seo', 'bing_site_verification'))"
                />
            </x-admin.field>
        </div>
    </x-admin.metabox>
</div>
