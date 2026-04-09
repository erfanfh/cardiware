@extends('admin.layout')

@section('title', 'تنظیمات صفحه نوشته ها')

@section('content')
    <x-admin.form action="{{ route('admin.settings.posts.store') }}" method="put">
        <x-admin.page>
            <div class="space-y-6">
                <x-admin.metabox title="باکس ابتدایی">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-admin.field
                            label="عنوان"
                        >
                            <x-admin.form.input
                                name="first_box_title"
                                value="{{ get_setting('posts', 'first_box_title') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="زیرعنوان"
                        >
                            <x-admin.form.input
                                name="first_box_subtitle"
                                value="{{ get_setting('posts', 'first_box_subtitle') }}"
                            />
                        </x-admin.field>

                        <x-admin.field
                            label="لیبل"
                        >
                            <x-admin.form.input
                                name="first_box_label"
                                value="{{ get_setting('posts', 'first_box_label') }}"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="سایر تنظیمات">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-admin.field
                            label="تعداد نوشته در هر صفحه"
                        >
                            <x-admin.form.input
                                name="posts_per_page"
                                value="{{ get_setting('posts', 'posts_per_page') }}"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>
            </div>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
