@extends('admin.layout')

@section('title', 'تنظیمات هدر')

@section('content')
    <x-admin.page>
        <div class="space-y-6">
            <x-admin.metabox title="لوگو">
                <div class="grid grid-cols-1 gap-5">
                    <x-admin.field
                        label="لوگوی هدر"
                        hint="ترجیحاً PNG یا WebP با پس‌زمینه شفاف."
                    >
                        <x-admin.form.input
                            type="file"
                            name="header_logo"
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
                            name="ajax_search_enabled"
                            :checked="true"
                        />
                    </x-admin.field>
                </div>
            </x-admin.metabox>
        </div>
    </x-admin.page>
@endsection
