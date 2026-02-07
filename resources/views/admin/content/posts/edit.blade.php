@extends('admin.layout')

@section('title', 'ویرایش نوشته')

@section('content')
    <x-admin.form-page
        title="ویرایش نوشته"
        subtitle="در این بخش می‌توانید اطلاعات نوشته را به‌روزرسانی کنید."
        :back-route="route('admin.posts.index')"
        back-label="بازگشت به لیست نوشته ها"
    >
        <x-slot:aside>
            <div class="za-aside-card">
                <h3>نکات ویرایش نوشته</h3>
                <ul>
                    <li>عنوان و متن نوشته الزامی می باشند.</li>
                    <li>برای بهبود تجربه کاربری، حجم تصاویر مورد استفاده را کم کنید.</li>
                </ul>
            </div>
        </x-slot:aside>

        <x-admin.validation-errors/>

        <x-admin.content.post-form
            :article="$post"
            mode="edit"
        />
    </x-admin.form-page>
@endsection
