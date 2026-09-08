@extends('admin.layout')

@section('title', 'تنظیمات صفحه تیم')

@section('content')
    <x-admin.form action="{{ route('admin.settings.team.store') }}" method="put">
        <x-admin.page>
            <x-admin.form.validation-errors class="mb-6"/>

            <div class="space-y-6">
                <x-admin.metabox title="بخش ابتدایی و Hero">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field label="برچسب بالای عنوان">
                            <x-admin.form.input name="hero_badge" :value="old('hero_badge', $teamSettings['hero_badge'])"/>
                        </x-admin.field>

                        <x-admin.field label="عنوان اصلی">
                            <x-admin.form.input name="hero_title" :value="old('hero_title', $teamSettings['hero_title'])"/>
                        </x-admin.field>

                        <x-admin.field class="md:col-span-2" label="توضیحات">
                            <x-admin.form.textarea name="hero_description" rows="3" :value="old('hero_description', $teamSettings['hero_description'])"/>
                        </x-admin.field>

                        <x-admin.field label="متن دکمه مشاهده اعضا">
                            <x-admin.form.input name="hero_primary_button_title" :value="old('hero_primary_button_title', $teamSettings['hero_primary_button_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="متن دکمه همکاری">
                            <x-admin.form.input name="hero_secondary_button_title" :value="old('hero_secondary_button_title', $teamSettings['hero_secondary_button_title'])"/>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="آمار زنده صفحه">
                    <div class="mb-5 rounded-2xl border border-sky-100 bg-sky-50 p-4 text-xs leading-6 text-sky-800">
                        تعداد اعضا از انتخاب شما و تعداد محصولات و محتوا از رکوردهای فعال سایت محاسبه می‌شود؛ فقط عنوان کارت‌ها و سال شروع قابل ویرایش است.
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                        <x-admin.field label="عنوان آمار اعضا">
                            <x-admin.form.input name="stats_members_title" :value="old('stats_members_title', $teamSettings['stats_members_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="عنوان سال شروع">
                            <x-admin.form.input name="stats_since_title" :value="old('stats_since_title', $teamSettings['stats_since_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="سال شروع فعالیت">
                            <x-admin.form.input type="number" name="stats_since_year" min="1300" max="1600" dir="ltr" :value="old('stats_since_year', $teamSettings['stats_since_year'])"/>
                        </x-admin.field>

                        <x-admin.field label="عنوان آمار محصولات">
                            <x-admin.form.input name="stats_products_title" :value="old('stats_products_title', $teamSettings['stats_products_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="عنوان آمار محتوا">
                            <x-admin.form.input name="stats_content_title" :value="old('stats_content_title', $teamSettings['stats_content_title'])"/>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="ویژگی‌های تیم">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field label="عنوان بخش">
                            <x-admin.form.input name="highlights_title" :value="old('highlights_title', $teamSettings['highlights_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="توضیحات بخش">
                            <x-admin.form.textarea name="highlights_description" rows="2" :value="old('highlights_description', $teamSettings['highlights_description'])"/>
                        </x-admin.field>
                    </div>

                    <div class="mt-5">
                        <x-admin.form.repeater
                            :items="old('highlights_items', $teamSettings['highlights_items'])"
                            name="highlights_items"
                            title="کارت‌های ویژگی"
                            addText="افزودن ویژگی"
                            emptyText="هنوز ویژگی‌ای ثبت نشده است."
                            headingKey="title"
                            headingPlaceholder="ویژگی بدون عنوان"
                            :defaultItem="['title' => '', 'description' => '']"
                        >
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <x-admin.field label="عنوان">
                                    <x-admin.form.input x-model="it.title" x-bind:name="field(i, 'title')"/>
                                </x-admin.field>

                                <x-admin.field class="md:col-span-2" label="توضیح کوتاه">
                                    <x-admin.form.textarea rows="2" x-model="it.description" x-bind:name="field(i, 'description')"/>
                                </x-admin.field>
                            </div>
                        </x-admin.form.repeater>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="اعضای نمایشی">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                        <x-admin.field label="عنوان بخش">
                            <x-admin.form.input name="members_title" :value="old('members_title', $teamSettings['members_title'])"/>
                        </x-admin.field>

                        <x-admin.field class="lg:col-span-2" label="توضیحات بخش">
                            <x-admin.form.textarea name="members_description" rows="2" :value="old('members_description', $teamSettings['members_description'])"/>
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-3"
                            label="انتخاب و ترتیب اعضا"
                            hint="ترتیب آیتم‌های انتخاب‌شده، ترتیب نمایش کارت‌ها در سایت است. حداکثر ۲۴ عضو قابل انتخاب است."
                        >
                            <x-admin.form.async-select
                                name="member_ids"
                                :multiple="true"
                                :value="old('member_ids', $teamSettings['member_ids'])"
                                :selected-options="$selectedTeamMemberOptions"
                                :search-url="route('admin.settings.team.ajax-members')"
                                placeholder="نام، موبایل یا ایمیل عضو را جستجو کنید..."
                                :max-items="24"
                                :error="$errors->first('member_ids')"
                            />
                        </x-admin.field>

                        <x-admin.field class="lg:col-span-2" label="متن حالت بدون عضو">
                            <x-admin.form.input name="members_empty_title" :value="old('members_empty_title', $teamSettings['members_empty_title'])"/>
                        </x-admin.field>

                        <div class="flex items-end">
                            <x-admin.form.button tag="a" href="{{ route('admin.team-members.index') }}" variant="secondary">
                                مدیریت اطلاعات اعضای تیم
                            </x-admin.form.button>
                        </div>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="فرآیند همکاری تیم">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field label="عنوان بخش">
                            <x-admin.form.input name="process_title" :value="old('process_title', $teamSettings['process_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="توضیحات بخش">
                            <x-admin.form.textarea name="process_description" rows="2" :value="old('process_description', $teamSettings['process_description'])"/>
                        </x-admin.field>
                    </div>

                    <div class="mt-5">
                        <x-admin.form.repeater
                            :items="old('process_items', $teamSettings['process_items'])"
                            name="process_items"
                            title="مراحل کار"
                            addText="افزودن مرحله"
                            emptyText="هنوز مرحله‌ای ثبت نشده است."
                            headingKey="title"
                            headingPlaceholder="مرحله بدون عنوان"
                            :defaultItem="['title' => '', 'description' => '']"
                        >
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <x-admin.field label="عنوان مرحله">
                                    <x-admin.form.input x-model="it.title" x-bind:name="field(i, 'title')"/>
                                </x-admin.field>

                                <x-admin.field class="md:col-span-2" label="توضیح مرحله">
                                    <x-admin.form.textarea rows="2" x-model="it.description" x-bind:name="field(i, 'description')"/>
                                </x-admin.field>
                            </div>
                        </x-admin.form.repeater>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="دعوت به همکاری">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field label="برچسب کوتاه">
                            <x-admin.form.input name="cta_badge" :value="old('cta_badge', $teamSettings['cta_badge'])"/>
                        </x-admin.field>

                        <x-admin.field label="عنوان">
                            <x-admin.form.input name="cta_title" :value="old('cta_title', $teamSettings['cta_title'])"/>
                        </x-admin.field>

                        <x-admin.field class="md:col-span-2" label="توضیحات">
                            <x-admin.form.textarea name="cta_description" rows="3" :value="old('cta_description', $teamSettings['cta_description'])"/>
                        </x-admin.field>

                        <x-admin.field label="متن دکمه اصلی">
                            <x-admin.form.input name="cta_primary_button_title" :value="old('cta_primary_button_title', $teamSettings['cta_primary_button_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="لینک دکمه اصلی" hint="آدرس کامل، لینک داخلی مانند /contact یا mailto مجاز است.">
                            <x-admin.form.input name="cta_primary_button_url" dir="ltr" placeholder="/contact" :value="old('cta_primary_button_url', $teamSettings['cta_primary_button_url'])"/>
                        </x-admin.field>

                        <x-admin.field label="متن دکمه دوم">
                            <x-admin.form.input name="cta_secondary_button_title" :value="old('cta_secondary_button_title', $teamSettings['cta_secondary_button_title'])"/>
                        </x-admin.field>

                        <x-admin.field label="لینک دکمه دوم">
                            <x-admin.form.input name="cta_secondary_button_url" dir="ltr" placeholder="/careers" :value="old('cta_secondary_button_url', $teamSettings['cta_secondary_button_url'])"/>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>
            </div>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
