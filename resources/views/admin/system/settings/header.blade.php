@extends('admin.layout')

@section('title', 'تنظیمات هدر')

@section('content')
    <x-admin.form action="{{ route('admin.settings.header.store') }}" method="put">
        <x-admin.page>
            <div class="space-y-6">
                <x-admin.metabox title="لوگو">
                    <div class="w-72 max-w-full">
                        <x-admin.field
                            label="لوگوی هدر"
                        >
                            <x-admin.form.media-picker
                                name="logo"
                                :mediaId="get_setting('header', 'logo')->id"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>

                <x-admin.metabox title="جستجو">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <x-admin.field
                            label="فعال بودن جستجوی Ajax"
                            hint="در صورت فعال بودن، نتایج جستجو به‌صورت زنده نمایش داده می‌شوند."
                        >
                            <x-admin.form.switch
                                name="ajax_search"
                                :checked="get_setting('header', 'ajax_search')"
                            />
                        </x-admin.field>
                    </div>
                </x-admin.metabox>
            </div>
        </x-admin.page>

        <x-admin.save-button/>
    </x-admin.form>
@endsection
