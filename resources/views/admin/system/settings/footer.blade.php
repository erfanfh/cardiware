@extends('admin.layout')

@section('title', 'تنظیمات فوتر')

@section('content')
    <x-admin.page>
        <div class="space-y-6">

            <x-admin.metabox title="درباره ما">
                <div class="grid grid-cols-1 gap-5">
                    <x-admin.field
                        label="متن درباره ما"
                        hint="این متن در بخش فوتر سایت نمایش داده می‌شود."
                    >
                        <x-admin.form.textarea
                            name="footer_about"
                            rows="5"
                            placeholder="متن درباره ما را وارد کنید..."
                        />
                    </x-admin.field>
                </div>
            </x-admin.metabox>

            <x-admin.metabox title="هویت و متن‌های فوتر">
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <x-admin.field
                        label="لوگوی فوتر"
                        hint="ترجیحاً PNG یا WebP با پس‌زمینه شفاف."
                    >
                        <x-admin.form.input
                            type="file"
                            name="footer_logo"
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="متن کپی‌رایت"
                        hint="این متن در پایین‌ترین بخش فوتر نمایش داده می‌شود."
                    >
                        <x-admin.form.input
                            name="footer_copyright"
                            placeholder="مثلاً تمامی حقوق برای مودینگ وی محفوظ است."
                        />
                    </x-admin.field>

                    <div class="lg:col-span-2">
                        <x-admin.field
                            label="متن هدف و کیفیت"
                            hint="متن معرفی کوتاه برای کیفیت خدمات یا هدف مجموعه."
                        >
                            <x-admin.form.textarea
                                name="footer_quality_text"
                                rows="4"
                                placeholder="متن هدف و کیفیت..."
                            />
                        </x-admin.field>
                    </div>

                    <x-admin.field
                        label="متن تعهد ما"
                    >
                        <x-admin.form.textarea
                            name="footer_commitment_text"
                            rows="4"
                            placeholder="متن تعهد ما..."
                        />
                    </x-admin.field>

                    <x-admin.field
                        label="متن مودینگ استور"
                    >
                        <x-admin.form.textarea
                            name="footer_store_text"
                            rows="4"
                            placeholder="متن مربوط به مودینگ استور..."
                        />
                    </x-admin.field>
                </div>
            </x-admin.metabox>

            <x-admin.metabox title="نمادها">
                <div class="space-y-4">
                    <div class="text-xs text-slate-500">
                        برای هر نماد می‌توانید تصویر و لینک مربوطه را ثبت کنید.
                    </div>

                    <x-admin.form.repeater
                        name="footer_badges"
                        addLabel="افزودن نماد"
                        emptyTitle="هنوز نمادی ثبت نشده است"
                        emptyDescription="از دکمه افزودن نماد برای ایجاد اولین آیتم استفاده کنید."
                    >
                        {{-- shell repeater items --}}
                    </x-admin.form.repeater>
                </div>
            </x-admin.metabox>

            <x-admin.metabox title="آدرس‌ها">
                <div class="space-y-4">
                    <div class="text-xs text-slate-500">
                        آدرس‌ها و اطلاعات تماس قابل نمایش در فوتر را از این بخش مدیریت کنید.
                    </div>

                    <x-admin.form.repeater
                        name="footer_addresses"
                        addLabel="افزودن آدرس"
                        emptyTitle="هنوز آدرسی ثبت نشده است"
                        emptyDescription="می‌توانید چند آدرس یا راه ارتباطی مختلف اضافه کنید."
                    >
                        {{-- shell repeater items --}}
                    </x-admin.form.repeater>
                </div>
            </x-admin.metabox>

            <x-admin.metabox title="شبکه‌های اجتماعی">
                <div class="space-y-4">
                    <div class="text-xs text-slate-500">
                        لینک شبکه‌های اجتماعی نمایش‌داده‌شده در فوتر را از این بخش تنظیم کنید.
                    </div>

                    <x-admin.form.repeater
                        name="footer_social_links"
                        addLabel="افزودن شبکه اجتماعی"
                        emptyTitle="هنوز شبکه اجتماعی ثبت نشده است"
                        emptyDescription="مثلاً تلگرام، اینستاگرام، یوتیوب و ..."
                    >
                        {{-- shell repeater items --}}
                    </x-admin.form.repeater>
                </div>
            </x-admin.metabox>

            <x-admin.metabox title="لینک‌های دسترسی سریع">
                <div class="space-y-4">
                    <div class="text-xs text-slate-500">
                        لینک‌های مهم و پرکاربرد فوتر را از این بخش وارد کنید.
                    </div>

                    <x-admin.form.repeater
                        name="footer_quick_links"
                        addLabel="افزودن لینک"
                        emptyTitle="هنوز لینکی ثبت نشده است"
                        emptyDescription="عنوان و لینک هر مورد را بعداً داخل آیتم‌های ریپیتر قرار می‌دهیم."
                    >
                        {{-- shell repeater items --}}
                    </x-admin.form.repeater>
                </div>
            </x-admin.metabox>

            <x-admin.metabox title="خدمات و محصولات">
                <div class="space-y-4">
                    <div class="text-xs text-slate-500">
                        لینک‌های مربوط به خدمات و محصولات قابل نمایش در فوتر.
                    </div>

                    <x-admin.form.repeater
                        name="footer_services_links"
                        addLabel="افزودن لینک خدمات/محصول"
                        emptyTitle="هنوز موردی ثبت نشده است"
                        emptyDescription="این بخش برای نمایش لینک‌های خدمات و محصولات فوتر استفاده می‌شود."
                    >
                        {{-- shell repeater items --}}
                    </x-admin.form.repeater>
                </div>
            </x-admin.metabox>

        </div>
    </x-admin.page>
@endsection
