@extends('admin.layout')

@section('title', 'ایجاد نوشته جدید')

@section('content')
    <x-admin.form-page
        title="ایجاد نوشته جدید"
        subtitle="اطلاعات نوشته جدید را وارد نمایید."
        :back-route="route('admin.posts.index')"
        back-label="بازگشت به لیست نوشته ها"
    >
        <x-slot:aside>
            <div class="bg-[var(--bg-card)] rounded-[var(--radius-xl)] pt-[18px] pb-[16px] px-[20px] border border-solid border-[var(--border-subtle)] shadow-[var(--shadow-soft)]">
                <h3 class="text-[0.9rem] font-bold text-[var(--text-main)] mb-[12px]">نکات ساخت نوشته</h3>
                <ul class="list-disc pr-5 space-y-2">
                    <li class="text-[0.75rem] text-[var(--text-main)] mb-[8px] relative text-justify" style="list-style: circle">عنوان و متن نوشته الزامی می باشند.</li>
                    <li class="text-[0.75rem] text-[var(--text-main)] mb-[8px] relative text-justify" style="list-style: circle">برای بهبود تجربه کاربری، حجم تصاویر مورد استفاده را کم کنید.</li>
                </ul>
            </div>
        </x-slot:aside>

        <x-admin.validation-errors/>

        <x-admin.content.post-form mode="create"/>
    </x-admin.form-page>
@endsection
