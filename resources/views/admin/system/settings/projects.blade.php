@extends('admin.layout')

@section('title', 'تنظیمات صفحه پروژه ها')

@section('content')
    <x-admin.form action="{{ route('admin.settings.projects.store') }}" method="put">
        <x-admin.page>
            <div class="space-y-6">
                <x-admin.metabox title="باکس ابتدایی">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="flex flex-col gap-4">
                            <x-admin.field
                                label="عنوان"
                            >
                                <x-admin.form.input
                                    name="first_box_title"
                                    value="{{ get_setting('projects', 'first_box_title') }}"
                                />
                            </x-admin.field>

                            <x-admin.field
                                label="زیرعنوان"
                            >
                                <x-admin.form.input
                                    name="first_box_subtitle"
                                    value="{{ get_setting('projects', 'first_box_subtitle') }}"
                                />
                            </x-admin.field>

                            <x-admin.field
                                label="لیبل"
                            >
                                <x-admin.form.input
                                    name="first_box_label"
                                    value="{{ get_setting('projects', 'first_box_label') }}"
                                />
                            </x-admin.field>
                        </div>

                        <x-admin.field
                            label="تصویر بک گراند"
                        >
                            <x-admin.form.media-picker
                                mode="field"
                                name="first_box_image"
                                :value="[
                                    'id' => old('first_box_image.id', get_setting('projects', 'first_box_image')?->id),
                                    'path' => old('first_box_image.path', get_setting('projects', 'first_box_image')?->path),
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
