@extends('admin.layout')

@section('title', $announcement ? 'ویرایش اطلاعیه حساب کاربری' : 'اطلاعیه جدید حساب کاربری')

@section('content')
    <x-admin.page>
        <x-admin.form :method="$announcement ? 'put' : 'post'"
                      :action="$announcement ? route('admin.account-announcements.update', $announcement) : route('admin.account-announcements.store')">
            <div class="space-y-5">
                <x-admin.form.validation-errors/>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h1 class="text-xl font-black text-slate-900">{{ $announcement ? 'ویرایش اطلاعیه حساب کاربری' : 'اطلاعیه جدید حساب کاربری' }}</h1>
                    <x-admin.form.button tag="a" href="{{ route('admin.account-announcements.index') }}"
                                         variant="secondary">بازگشت به فهرست
                    </x-admin.form.button>
                </div>
                <x-admin.metabox title="محتوای اطلاعیه">
                    <div class="space-y-5">
                        <x-admin.field label="عنوان" required :error="$errors->first('title')">
                            <x-admin.form.input name="title" :value="old('title', $announcement?->title)"
                                                maxlength="180" required/>
                        </x-admin.field>
                        <x-admin.field label="متن اطلاعیه" required :error="$errors->first('body')">
                            <x-admin.form.textarea name="body" :value="old('body', $announcement?->body)" rows="7"
                                                   maxlength="5000" required/>
                        </x-admin.field>
                        <x-admin.field label="نوع اطلاعیه" required :error="$errors->first('type')">
                            <x-admin.form.select name="type" :value="old('type', $announcement?->type ?? 'info')"
                                                 :options="\App\Domain\Notification\Models\AccountAnnouncement::TYPES"/>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>
                <x-admin.metabox title="انتشار و زمان‌بندی">
                    <x-admin.field label="نمایش در حساب همه کاربران" :error="$errors->first('is_active')">
                        <x-admin.form.switch name="is_active"
                                             :checked="old('is_active', $announcement?->is_active ?? false)"/>
                        <p class="mt-2 text-xs leading-6 text-slate-500">با فعال‌کردن، اطلاعیه در بازه انتخاب‌شده بالای
                            صفحات حساب کاربران نمایش داده می‌شود. اطلاعیه غیرفعال فقط در پنل مدیریت دیده می‌شود.</p>
                    </x-admin.field>
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <x-admin.field label="شروع نمایش" :error="$errors->first('starts_at')">
                            <x-admin.form.date-picker name="starts_at" dir="ltr" placeholder=""
                                                      :value="old('starts_at', $announcement?->starts_at ? jalali_date($announcement->starts_at) : '')"/>
                        </x-admin.field>
                        <x-admin.field label="پایان نمایش" :error="$errors->first('ends_at')">
                            <x-admin.form.date-picker name="ends_at" dir="ltr" placeholder=""
                                                      :value="old('ends_at', $announcement?->ends_at ? jalali_date($announcement->ends_at) : '')"/>
                        </x-admin.field>
                    </div>
                </x-admin.metabox>
                <x-admin.form.save-button :label="$announcement ? 'ذخیره تغییرات' : 'ایجاد اطلاعیه'"/>
            </div>
        </x-admin.form>
    </x-admin.page>
@endsection
