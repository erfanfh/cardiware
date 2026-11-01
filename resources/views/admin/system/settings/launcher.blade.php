@extends('admin.layout')

@section('title', 'تنظیمات لانچر')

@section('content')
    <x-admin.form action="{{ route('admin.settings.launcher.store') }}" method="put">
        <x-admin.page>
            <div class="space-y-6">
                <x-admin.metabox title="متن‌های اصلی صفحه لانچر">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <x-admin.field label="متن نشان (Badge)">
                            <x-admin.form.input
                                name="badge_text"
                                value="{{ old('badge_text', get_setting('launcher', 'badge_text', 'نسخه رسمی مودینگ‌وی')) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="عنوان اصلی">
                            <x-admin.form.input
                                name="title"
                                value="{{ old('title', get_setting('launcher', 'title', 'لانچر اختصاصی مودینگ‌وی')) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="عنوان فرعی">
                            <x-admin.form.input
                                name="subtitle"
                                value="{{ old('subtitle', get_setting('launcher', 'subtitle', 'دانلود و نصب MW Launcher')) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="متن دکمه دانلود">
                            <x-admin.form.input
                                name="download_button_text"
                                value="{{ old('download_button_text', get_setting('launcher', 'download_button_text', 'دانلود لانچر ویندوز')) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="متن دکمه بازگشت">
                            <x-admin.form.input
                                name="back_button_text"
                                value="{{ old('back_button_text', get_setting('launcher', 'back_button_text', 'بازگشت به سایت')) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="توضیح کوتاه" class="lg:col-span-2">
                            <x-admin.form.textarea
                                name="description"
                                rows="3"
                            >{{ old('description', get_setting('launcher', 'description', 'تجربه‌ای جدید، آسان و سریع از نصب پچ‌ها و بازی‌ها فقط با یک کلیک')) }}</x-admin.form.textarea>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="انیمیشن ماتریکس">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                        <x-admin.field label="فعال بودن انیمیشن ماتریکس" class="switch-field">
                            <x-admin.form.switch
                                name="matrix_enabled"
                                :checked="old('matrix_enabled', get_setting('launcher', 'matrix_enabled', 1))"
                            />
                        </x-admin.field>

                        <x-admin.field label="حروف انیمیشن (به انگلیسی)">
                            <x-admin.form.input
                                name="matrix_letters"
                                value="{{ old('matrix_letters', get_setting('launcher', 'matrix_letters', 'MODDINGWAYLAUNCHER')) }}"
                                class="direction-ltr"
                            />
                        </x-admin.field>

                        <x-admin.field label="سرعت (فریم‌برثانیه — ۱۵ تا ۶۰)">
                            <x-admin.form.input
                                name="matrix_speed"
                                type="number"
                                value="{{ old('matrix_speed', get_setting('launcher', 'matrix_speed', 24)) }}"
                            />
                        </x-admin.field>
                    </div>

                    <div class="text-xs text-slate-500">
                        انیمیشن سبک ماتریکس با حروف انتخابی شما جایگزین افکت سنگین قبلی (WebGL) شده است؛ روی دستگاه‌های ضعیف هم روان اجرا می‌شود.
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="ویژگی‌ها">
                    <x-admin.form.repeater
                        name="features"
                        :items="old('features', get_setting('launcher', 'features') ?? [])"
                        title="ویژگی‌های لانچر"
                        addText="افزودن ویژگی"
                        emptyText="ویژگی‌ای اضافه نشده است."
                        headingKey="label"
                        headingPlaceholder="ویژگی بدون عنوان"
                        :defaultItem="['label' => '']"
                    >
                        <div class="grid grid-cols-1 gap-4">
                            <x-admin.field label="عنوان ویژگی">
                                <x-admin.form.input
                                    x-bind:name="field(i, 'label')"
                                    x-model="it.label"
                                />
                            </x-admin.field>
                        </div>
                    </x-admin.form.repeater>
                </x-admin.metabox>

                <x-admin.metabox title="تصاویر پیش‌نمایش">
                    <div class="space-y-4">
                        <x-admin.field label="عنوان بخش پیش‌نمایش">
                            <x-admin.form.input
                                name="previews_title"
                                value="{{ old('previews_title', get_setting('launcher', 'previews_title', 'نگاهی به داخل لانچر')) }}"
                            />
                        </x-admin.field>

                        <x-admin.form.repeater
                            name="previews"
                            :items="old('previews', get_setting('launcher', 'previews') ?? [])"
                            title="تصاویر پیش‌نمایش"
                            addText="افزودن تصویر"
                            emptyText="تصویری اضافه نشده است."
                            headingKey="title"
                            headingPlaceholder="تصویر بدون عنوان"
                            :defaultItem="[
                                'title' => '',
                                'image_id' => null,
                                'image_path' => null,
                            ]"
                        >
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <x-admin.field label="عنوان تصویر">
                                    <x-admin.form.input
                                        x-bind:name="field(i, 'title')"
                                        x-model="it.title"
                                    />
                                </x-admin.field>

                                <x-admin.field label="تصویر">
                                    <x-admin.form.media-picker
                                        mode="repeater"
                                        name="image"
                                    />
                                </x-admin.field>
                            </div>
                        </x-admin.form.repeater>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="باکس آموزش">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <x-admin.field label="عنوان آموزش">
                            <x-admin.form.input
                                name="tutorial_title"
                                value="{{ old('tutorial_title', get_setting('launcher', 'tutorial_title', 'چطور شروع کنم؟')) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="لینک ویدیو (اختیاری — آپارات/یوتیوب)">
                            <x-admin.form.input
                                name="tutorial_video_url"
                                value="{{ old('tutorial_video_url', get_setting('launcher', 'tutorial_video_url')) }}"
                                class="direction-ltr"
                            />
                        </x-admin.field>

                        <x-admin.field label="توضیح آموزش" class="lg:col-span-2">
                            <x-admin.form.textarea
                                name="tutorial_description"
                                rows="3"
                            >{{ old('tutorial_description', get_setting('launcher', 'tutorial_description', 'در سه قدم ساده لانچر را نصب کنید و اولین پچ را دانلود کنید.')) }}</x-admin.form.textarea>
                        </x-admin.field>

                        <x-admin.field label="متن دکمه آموزش">
                            <x-admin.form.input
                                name="tutorial_button_text"
                                value="{{ old('tutorial_button_text', get_setting('launcher', 'tutorial_button_text', 'مشاهده آموزش کامل')) }}"
                            />
                        </x-admin.field>

                        <x-admin.field label="لینک دکمه آموزش">
                            <x-admin.form.input
                                name="tutorial_button_url"
                                value="{{ old('tutorial_button_url', get_setting('launcher', 'tutorial_button_url')) }}"
                                class="direction-ltr"
                            />
                        </x-admin.field>
                    </div>

                    <div class="mt-4">
                        <x-admin.form.repeater
                            name="tutorial_steps"
                            :items="old('tutorial_steps', get_setting('launcher', 'tutorial_steps') ?? [])"
                            title="مراحل آموزش"
                            addText="افزودن مرحله"
                            emptyText="مرحله‌ای اضافه نشده است."
                            headingKey="title"
                            headingPlaceholder="مرحله بدون عنوان"
                            :defaultItem="[
                                'title' => '',
                                'description' => '',
                            ]"
                        >
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                <x-admin.field label="عنوان مرحله">
                                    <x-admin.form.input
                                        x-bind:name="field(i, 'title')"
                                        x-model="it.title"
                                    />
                                </x-admin.field>

                                <x-admin.field label="توضیح مرحله" class="lg:col-span-2">
                                    <x-admin.form.input
                                        x-bind:name="field(i, 'description')"
                                        x-model="it.description"
                                    />
                                </x-admin.field>
                            </div>
                        </x-admin.form.repeater>
                    </div>
                </x-admin.metabox>
            </div>
        </x-admin.page>
    </x-admin.form>
@endsection
