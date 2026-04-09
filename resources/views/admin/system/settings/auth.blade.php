@extends('admin.layout')

@section('title', 'تنظیمات درباره ما')

@section('content')
    <x-admin.form action="{{ route('admin.settings.auth.store') }}" method="put">
        <x-admin.page>
            <div class="space-y-6">
                <x-admin.metabox title="اطلاعات باکس هدر" :collapsible="false">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-4">
                            <x-admin.field
                                label="لیبل"
                            >
                                <x-admin.form.input
                                    name="label"
                                    value="{{ get_setting('auth', 'label') }}"
                                />
                            </x-admin.field>

                            <x-admin.field
                                label="متن دکمه"
                            >
                                <x-admin.form.input
                                    name="button_title"
                                    value="{{ get_setting('auth', 'button_title') }}"
                                />
                            </x-admin.field>

                            <x-admin.field
                                label="لینک دکمه"
                            >
                                <x-admin.form.input
                                    name="button_link"
                                    value="{{ get_setting('auth', 'button_link') }}"
                                />
                            </x-admin.field>
                        </div>

                        <x-admin.form.media-picker
                            mode="field"
                            name="image"
                            :value="[
                                    'id' => old('image.id', get_setting('auth', 'image')?->id),
                                    'path' => old('image.path', get_setting('auth', 'image')?->path),
                                ]"
                        />
                    </div>
                </x-admin.metabox>
            </div>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection
