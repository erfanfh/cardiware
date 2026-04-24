@extends('admin.layout')

@section('title', 'تنظیمات فوتر')

@section('content')
    <x-admin.form action="{{ route('admin.settings.footer.store') }}" method="put">
        <x-admin.page>
            <div class="space-y-6">
                <x-admin.metabox title="هویت و متن‌های فوتر">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-4">
                        <x-admin.field
                            label="لوگوی فوتر"
                        >
                            <x-admin.form.media-picker
                                name="logo"
                                :value="[
                                    'id' => old('logo.id', get_setting('footer', 'logo')->id),
                                    'path' => old('logo.path', get_setting('footer', 'logo')->path),
                                ]"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-3"
                            label="متن درباره ما"
                        >
                            <x-admin.form.textarea
                                name="about"
                                rows="5"
                                value="{{ get_setting('footer', 'about') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-4"
                            label="متن کپی‌رایت"
                        >
                            <x-admin.form.input
                                name="copyright"
                                value="{{ get_setting('footer', 'copyright') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="عنوان باکس هدف و کیفیت"
                        >
                            <x-admin.form.input
                                name="quality_title"
                                value="{{ get_setting('footer', 'quality_title') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-3"
                            label="متن باکس هدف و کیفیت"
                        >
                            <x-admin.form.textarea
                                name="quality_text"
                                rows="4"
                                value="{{ get_setting('footer', 'quality_text') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="عنوان باکس تعهد ما"
                        >
                            <x-admin.form.input
                                name="commitment_title"
                                value="{{ get_setting('footer', 'commitment_title') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-3"
                            label="متن باکس تعهد ما"
                        >
                            <x-admin.form.textarea
                                name="commitment_text"
                                rows="4"
                                value="{{ get_setting('footer', 'commitment_text') }}"
                            />
                        </x-admin.field>

                        <div class="flex flex-col gap-4">
                            <x-admin.field
                                label="عنوان باکس فروشگاه"
                            >
                                <x-admin.form.input
                                    name="store_title"
                                    value="{{ get_setting('footer', 'store_title') }}"
                                />
                            </x-admin.field>

                            <x-admin.field
                                label="لینک فروشگاه"
                            >
                                <x-admin.form.input
                                    name="store_url"
                                    value="{{ get_setting('footer', 'store_url') }}"
                                    class="direction-ltr"
                                />
                            </x-admin.field>
                        </div>

                        <x-admin.field
                            class="lg:col-span-3"
                            label="متن باکس فروشگاه"
                        >
                            <x-admin.form.textarea
                                name="store_text"
                                rows="4"
                                value="{{ get_setting('footer', 'store_text') }}"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="ستون 1 - ارتباط با ما">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-4">
                        <x-admin.field
                            class="lg:col-span-4"
                            label="عنوان ستون"
                        >
                            <x-admin.form.input
                                name="column1_title"
                                value="{{ get_setting('footer', 'column1_title') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آدرس ایمیل"
                        >
                            <x-admin.form.input
                                name="email"
                                class="direction-ltr"
                                value="{{ get_setting('footer', 'email') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="شماره تماس"
                        >
                            <x-admin.form.input
                                name="phone"
                                class="direction-ltr"
                                value="{{ get_setting('footer', 'phone') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آی دی تلگرام"
                        >
                            <x-admin.form.input
                                name="telegram"
                                class="direction-ltr"
                                value="{{ get_setting('footer', 'telegram') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آی دی اینستاگرام"
                        >
                            <x-admin.form.input
                                name="instagram"
                                class="direction-ltr"
                                value="{{ get_setting('footer', 'instagram') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آدرس 1"
                        >
                            <x-admin.form.input
                                name="address_1"
                                value="{{ get_setting('footer', 'address_1') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آدرس 2"
                        >
                            <x-admin.form.input
                                name="address_2"
                                value="{{ get_setting('footer', 'address_2') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آدرس 3"
                        >
                            <x-admin.form.input
                                name="address_3"
                                value="{{ get_setting('footer', 'address_3') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-2"
                            label="آدرس 4"
                        >
                            <x-admin.form.input
                                name="address_4"
                                value="{{ get_setting('footer', 'address_4') }}"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="ستون 2 - خدمات و محصولات">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-4">
                        <x-admin.field
                            class="lg:col-span-4"
                            label="عنوان ستون"
                        >
                            <x-admin.form.input
                                name="column2_title"
                                value="{{ get_setting('footer', 'column2_title') }}"
                            />
                        </x-admin.field>

                        <div class="mt-5 lg:col-span-4">
                            <x-admin.form.repeater
                                :items="get_setting('footer', 'column2_items')"
                                name="column2_items"
                                title="آیتم ها"
                                addText="افزودن آیتم"
                                :defaultItem="[
                                    'title' => '',
                                    'url' => '',
                                ]"
                            >
                                <div class="grid grid-cols-12 gap-3">
                                    <div class="col-span-6">
                                        <x-admin.field label="عنوان" required>
                                            <x-admin.form.input x-model="it.title" x-bind:name="field(i, 'title')"/>
                                        </x-admin.field>
                                    </div>

                                    <div class="col-span-6">
                                        <x-admin.field label="لینک" required>
                                            <x-admin.form.input x-model="it.url"
                                                                x-bind:name="field(i, 'url')"
                                                                class="direction-ltr"/>
                                        </x-admin.field>
                                    </div>
                                </div>
                            </x-admin.form.repeater>
                        </div>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="ستون 3 - دسترسی سریع">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-4">
                        <x-admin.field
                            class="lg:col-span-4"
                            label="عنوان ستون"
                        >
                            <x-admin.form.input
                                name="column3_title"
                                value="{{ get_setting('footer', 'column3_title') }}"
                            />
                        </x-admin.field>

                        <div class="mt-5 lg:col-span-4">
                            <x-admin.form.repeater
                                :items="get_setting('footer', 'column3_items')"
                                name="column3_items"
                                title="آیتم ها"
                                addText="افزودن آیتم"
                                :defaultItem="[
                                    'title' => '',
                                    'url' => '',
                                ]"
                            >
                                <div class="grid grid-cols-12 gap-3">
                                    <div class="col-span-6">
                                        <x-admin.field label="عنوان" required>
                                            <x-admin.form.input x-model="it.title" x-bind:name="field(i, 'title')"/>
                                        </x-admin.field>
                                    </div>

                                    <div class="col-span-6">
                                        <x-admin.field label="لینک" required>
                                            <x-admin.form.input x-model="it.url"
                                                                x-bind:name="field(i, 'url')"
                                                                class="direction-ltr"/>
                                        </x-admin.field>
                                    </div>
                                </div>
                            </x-admin.form.repeater>
                        </div>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="ستون 4 - نماد ها">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-4">
                        <x-admin.field
                            class="lg:col-span-4"
                            label="عنوان ستون"
                        >
                            <x-admin.form.input
                                name="column4_title"
                                value="{{ get_setting('footer', 'column4_title') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            class="lg:col-span-4"
                            label="تصویر نمادها"
                        >
                            <x-admin.form.media-picker
                                name="column4_image"
                                :value="[
                                    'id' => old('column4_image.id', get_setting('footer', 'column4_image')->id),
                                    'path' => old('column4_image.path', get_setting('footer', 'column4_image')->path),
                                ]"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>
            </div>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
