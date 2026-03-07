@extends('admin.layout')

@section('title', 'ایجاد صفحه جدید')

@section('content')
    <x-admin.form-page
        title="ایجاد صفحه جدید"
        subtitle="اطلاعات صفحه جدید را وارد نمایید."
        :back-route="route('admin.pages.index')"
        back-label="بازگشت به لیست صفحه ها"
    >
        <x-slot:aside>
            <div class="bg-[var(--bg-card)] dark:bg-gray-800 rounded-[var(--radius-xl)] pt-[18px] pb-[16px] px-[20px] border border-solid border-[var(--border-subtle)] dark:border-gray-700 shadow-[var(--shadow-soft)]">
                <h3 class="text-[0.9rem] font-bold text-[var(--text-main)] dark:text-gray-100 mb-[12px]">نکات ساخت صفحه</h3>
                <ul class="list-disc pr-5 space-y-2">
                    <li class="text-[0.75rem] text-[var(--text-main)] dark:text-gray-300 mb-[8px] relative text-justify" style="list-style: circle">عنوان و متن صفحه الزامی می باشند.</li>
                    <li class="text-[0.75rem] text-[var(--text-main)] dark:text-gray-300 mb-[8px] relative text-justify" style="list-style: circle">برای بهبود تجربه کاربری، حجم تصاویر مورد استفاده را کم کنید.</li>
                </ul>
            </div>
        </x-slot:aside>

        <x-admin.validation-errors/>

        <x-admin.content.page-form mode="create"/>
    </x-admin.form-page>
@endsection
