@extends('admin.layout')

@section('title', 'تنظیمات صفحه ویدیوها')

@php($videoSettingsDefaults = \App\Infrastructure\Services\Setting\Groups\VideosSettings::defaults())

@section('content')
    <x-admin.form action="{{ route('admin.settings.videos.store') }}" method="put">
        <x-admin.page>
            <div class="space-y-6">
                <x-admin.metabox title="بخش ابتدایی و Hero">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <div class="space-y-4">
                            <x-admin.field label="برچسب بالای عنوان">
                                <x-admin.form.input name="hero_badge" value="{{ old('hero_badge', get_setting('videos', 'hero_badge') ?: $videoSettingsDefaults['hero_badge']) }}"/>
                            </x-admin.field>

                            <x-admin.field label="عنوان اصلی">
                                <x-admin.form.input name="hero_title" value="{{ old('hero_title', get_setting('videos', 'hero_title') ?: $videoSettingsDefaults['hero_title']) }}"/>
                            </x-admin.field>

                            <x-admin.field label="توضیحات">
                                <x-admin.form.textarea name="hero_description" rows="4" value="{{ old('hero_description', get_setting('videos', 'hero_description') ?: $videoSettingsDefaults['hero_description']) }}"/>
                            </x-admin.field>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <x-admin.field label="متن دکمه مشاهده ویدیوها">
                                    <x-admin.form.input name="hero_primary_button_title" value="{{ old('hero_primary_button_title', get_setting('videos', 'hero_primary_button_title') ?: $videoSettingsDefaults['hero_primary_button_title']) }}"/>
                                </x-admin.field>
                                <x-admin.field label="متن دکمه محبوب‌ترین‌ها">
                                    <x-admin.form.input name="hero_secondary_button_title" value="{{ old('hero_secondary_button_title', get_setting('videos', 'hero_secondary_button_title') ?: $videoSettingsDefaults['hero_secondary_button_title']) }}"/>
                                </x-admin.field>
                            </div>
                        </div>

                        <x-admin.field label="تصویر پس‌زمینه Hero">
                            <x-admin.form.media-picker
                                mode="field"
                                name="hero_image"
                                :value="[
                                    'id' => old('hero_image.id', get_setting('videos', 'hero_image')?->id),
                                    'path' => old('hero_image.path', get_setting('videos', 'hero_image')?->path),
                                ]"
                            />
                        </x-admin.field>

                        <x-admin.field label="برچسب ویدیوی منتخب">
                            <x-admin.form.input name="featured_badge" value="{{ old('featured_badge', get_setting('videos', 'featured_badge') ?: $videoSettingsDefaults['featured_badge']) }}"/>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="آرشیو و بخش‌های صفحه">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field label="عنوان آرشیو">
                            <x-admin.form.input name="archive_title" value="{{ old('archive_title', get_setting('videos', 'archive_title') ?: $videoSettingsDefaults['archive_title']) }}"/>
                        </x-admin.field>
                        <x-admin.field label="عنوان ویدیوهای ترند">
                            <x-admin.form.input name="trending_title" value="{{ old('trending_title', get_setting('videos', 'trending_title') ?: $videoSettingsDefaults['trending_title']) }}"/>
                        </x-admin.field>
                        <x-admin.field class="md:col-span-2" label="توضیحات آرشیو">
                            <x-admin.form.textarea name="archive_description" rows="3" value="{{ old('archive_description', get_setting('videos', 'archive_description') ?: $videoSettingsDefaults['archive_description']) }}"/>
                        </x-admin.field>
                        <x-admin.field label="عنوان دسته‌بندی‌ها">
                            <x-admin.form.input name="categories_title" value="{{ old('categories_title', get_setting('videos', 'categories_title') ?: $videoSettingsDefaults['categories_title']) }}"/>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="دعوت به دنبال‌کردن و حالت خالی">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-admin.field label="عنوان دعوت به دنبال‌کردن">
                            <x-admin.form.input name="follow_title" value="{{ old('follow_title', get_setting('videos', 'follow_title') ?: $videoSettingsDefaults['follow_title']) }}"/>
                        </x-admin.field>
                        <x-admin.field label="متن دکمه دنبال‌کردن">
                            <x-admin.form.input name="follow_button_title" value="{{ old('follow_button_title', get_setting('videos', 'follow_button_title') ?: $videoSettingsDefaults['follow_button_title']) }}"/>
                        </x-admin.field>
                        <x-admin.field class="md:col-span-2" label="توضیحات دعوت به دنبال‌کردن">
                            <x-admin.form.textarea name="follow_description" rows="3" value="{{ old('follow_description', get_setting('videos', 'follow_description') ?: $videoSettingsDefaults['follow_description']) }}"/>
                        </x-admin.field>
                        <x-admin.field class="md:col-span-2" label="لینک کانال یا شبکه اجتماعی">
                            <x-admin.form.input name="follow_button_url" dir="ltr" value="{{ old('follow_button_url', get_setting('videos', 'follow_button_url')) }}"/>
                        </x-admin.field>
                        <x-admin.field label="عنوان حالت بدون نتیجه">
                            <x-admin.form.input name="empty_title" value="{{ old('empty_title', get_setting('videos', 'empty_title') ?: $videoSettingsDefaults['empty_title']) }}"/>
                        </x-admin.field>
                        <x-admin.field label="توضیحات حالت بدون نتیجه">
                            <x-admin.form.input name="empty_description" value="{{ old('empty_description', get_setting('videos', 'empty_description') ?: $videoSettingsDefaults['empty_description']) }}"/>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>
            </div>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
