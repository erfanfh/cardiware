@extends('admin.layout')

@section('title', 'تنظیمات عمومی')

@section('content')
    <x-admin.form action="{{ route('admin.settings.general.store') }}" method="put">
        <x-admin.page>
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

                <x-admin.metabox title="سامانه‌های پیامکی">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <x-admin.field
                            label="سرویس پیش‌فرض"
                        >
                            <x-admin.form.select
                                name="default_sms_provider"
                                placeholder="انتخاب سرویس"
                                :options="[
                            \App\Infrastructure\Services\Sms\Providers\SmsirProvider::class => 'اس ام اس دات آی آر (SMS.ir)',
                            \App\Infrastructure\Services\Sms\Providers\MelliPayamakProvider::class => 'ملی پیامک',
                            \App\Infrastructure\Services\Sms\Providers\FarazSmsProvider::class => 'فراز اس ام اس',
                            ]"
                                value="{{ get_setting('general', 'default_sms_provider') }}"
                            />
                        </x-admin.field>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 mt-3">
                        <x-admin.field
                            label="کد قالب sms.ir"
                        >
                            <x-admin.form.input
                                name="smsir_tepmlate_id"
                                value="{{ get_setting('general', 'smsir_tepmlate_id') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="کلید توکن sms.ir"
                        >
                            <x-admin.form.input
                                name="smsir_api_token"
                                value="{{ get_setting('general', 'smsir_api_token') }}"
                            />
                        </x-admin.field>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-3 mt-3">
                        <x-admin.field
                            label="کد قالب فراز اس ام اس"
                        >
                            <x-admin.form.input
                                name="farazsms_tepmlate_id"
                                value="{{ get_setting('general', 'farazsms_tepmlate_id') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="کلید توکن فراز اس ام اس"
                        >
                            <x-admin.form.input
                                name="farazsms_api_token"
                                value="{{ get_setting('general', 'farazsms_api_token') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="شماره موبایل فراز اس ام اس"
                        >
                            <x-admin.form.input
                                name="farazsms_from_mobile"
                                value="{{ get_setting('general', 'farazsms_from_mobile') }}"
                            />
                        </x-admin.field>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-3 mt-3">
                        <x-admin.field
                            label="نام کاربری ملی پیامک"
                        >
                            <x-admin.form.input
                                name="melli_payamak_username"
                                value="{{ get_setting('general', 'melli_payamak_username') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="رمزعبور ملی پیامک"
                        >
                            <x-admin.form.input
                                name="melli_payamak_password"
                                value="{{ get_setting('general', 'melli_payamak_password') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="شماره موبایل ملی پیامک"
                        >
                            <x-admin.form.input
                                name="melli_payamak_from_mobile"
                                value="{{ get_setting('general', 'melli_payamak_from_mobile') }}"
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
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="نتایج جستجو">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
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
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
