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
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 shadow">
                <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 mb-3">نکات ساخت نوشته</h3>
                <ul class="list-disc pr-5 space-y-2">
                    <li class="text-sm text-gray-600 dark:text-gray-400">عنوان و متن نوشته الزامی می باشند.</li>
                    <li class="text-sm text-gray-600 dark:text-gray-400">برای بهبود تجربه کاربری، حجم تصاویر مورد استفاده را کم کنید.</li>
                </ul>
            </div>
        </x-slot:aside>

        <x-admin.validation-errors/>

        <x-admin.content.post-form mode="create"/>
    </x-admin.form-page>
@endsection
