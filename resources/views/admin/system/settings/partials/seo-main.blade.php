<div class="space-y-6">
    <x-admin.metabox title="تنظیمات اصلی" :collapsible="false">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 space-y-4">
            <x-admin.field label="نام سایت">
                <x-admin.form.input
                    name="site_name"
                    :value="old('site_name', get_setting('seo', 'site_name'))"
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
                hint="مقدار باید به صورت fa_IR یا en_US باشد"
            >
                <x-admin.form.input
                    name="og_locale"
                    class="direction-ltr"
                    :value="old('og_locale', get_setting('seo', 'og_locale'))"
                />
            </x-admin.field>

            <x-admin.field
                label="نوع کارت توییتر (Twitter Card)"
                hint="summary_large_image یا summary"
            >
                <x-admin.form.input
                    name="twitter_card"
                    class="direction-ltr"
                    :value="old('twitter_card', get_setting('seo', 'twitter_card'))"
                />
            </x-admin.field>

            <x-admin.field label="تصویر پیش‌فرض Open Graph">
                <x-admin.form.input
                    name="og_image"
                    class="direction-ltr"
                    :value="old('og_image', get_setting('seo', 'og_image'))"
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
