@extends('admin.layout')

@section('title', 'ویرایش برگه')

@section('content')
    <div data-edit-lock-status class="rounded-xl border border-slate-200 bg-white p-4 text-sm leading-7" role="status" aria-live="polite">
        ویرایش این برگه در اختیار شماست. تا زمانی که این صفحه باز است، مدیر دیگری نمی‌تواند آن را ویرایش کند.
    </div>
    <x-admin.form action="{{ route('admin.pages.update', $page) }}" method="put"
                  data-edit-lock
                  data-renew-url="{{ route('admin.pages.edit-lock.renew', $page) }}"
                  data-release-url="{{ route('admin.pages.edit-lock.release', $page) }}">
        <input type="hidden" name="edit_lock_token" value="{{ $editLockToken }}">
        @include('admin.content.pages.partials.form-fields', [
            'page' => $page,
        ])

        <x-admin.form.save-button label="ذخیره"/>
    </x-admin.form>
@endsection
