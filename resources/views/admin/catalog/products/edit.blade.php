@extends('admin.layout')

@section('title', 'ویرایش محصول')

@section('content')
    <div data-edit-lock-status class="rounded-xl border border-slate-200 bg-white p-4 text-sm leading-7" role="status" aria-live="polite">
        ویرایش این محصول در اختیار شماست. تا زمانی که این صفحه باز است، مدیر دیگری نمی‌تواند آن را ویرایش کند.
    </div>
    <x-admin.form action="{{ route('admin.products.update', $product) }}" method="put"
                  data-edit-lock
                  data-renew-url="{{ route('admin.products.edit-lock.renew', $product) }}"
                  data-release-url="{{ route('admin.products.edit-lock.release', $product) }}">
        <input type="hidden" name="edit_lock_token" value="{{ $editLockToken }}">
        @include('admin.catalog.products.partials.form-fields', [
            'product' => $product,
        ])

        <x-admin.form.save-button label="ذخیره"/>
    </x-admin.form>
@endsection
